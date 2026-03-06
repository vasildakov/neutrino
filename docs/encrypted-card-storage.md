# Storing Encrypted Credit Cards - Implementation Guide

## ⚠️ CRITICAL WARNING

**Before implementing encrypted card storage, READ THIS:**

### Should You Store Cards At All?

**For 99% of applications, the answer is NO!**

Instead, use **payment gateway tokenization**:
- ✅ Stripe: Customer & Payment Method API
- ✅ PayPal: Vault API
- ✅ Braintree: Customer Vault
- ✅ Square: Card on File

**Benefits of gateway tokenization:**
- ✅ No PCI DSS Level 1 certification required (SAQ A)
- ✅ No encryption key management
- ✅ No annual security audits
- ✅ No liability for data breaches
- ✅ Gateway handles all compliance

**ONLY store encrypted cards if:**
- You need multi-gateway support
- You require offline payment processing
- You have PCI DSS Level 1 certification
- You have dedicated security team
- You have annual budget for security audits ($20k-$50k+)

---

## Implementation Overview

If you've read the warning above and still need to store encrypted cards, here's what's been implemented:

### Components Created:

1. **CardTokenizationService** - Handles encryption/decryption
2. **PaymentCard Entity** - Database model for stored cards
3. **Migration** - Database schema
4. **Key Generator** - Creates secure encryption keys

---

## Setup Instructions

### Step 1: Generate Encryption Key

```bash
# Generate a new 256-bit encryption key
docker exec neutrino_php php bin/generate-encryption-key.php
```

This will output something like:
```
CARD_ENCRYPTION_KEY="aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890ABCD=="
```

### Step 2: Add Key to .env

**NEVER commit this key to version control!**

```dotenv
# .env
CARD_ENCRYPTION_KEY="[your-generated-key-here]"
```

Add to `.gitignore`:
```
.env
.env.local
```

### Step 3: Run Migration

```bash
docker exec neutrino_php vendor/bin/laminas doctrine:migrations:migrate
```

This creates the `payment_cards` table.

---

## Usage Examples

### Tokenize and Store a Card

```php
use Neutrino\Domain\Payment\PaymentCard;use Neutrino\Service\Card\CardTokenizationService;

// Get tokenization service
$tokenizer = $container->get(CardTokenizationService::class);

// Tokenize card data
$cardData = [
    'number' => '4111111111111111',
    'expiry_month' => '12',
    'expiry_year' => '2028',
    'name' => 'John Smith',
];

$tokenized = $tokenizer->tokenize($cardData);

// Create PaymentCard entity
$paymentCard = new PaymentCard(
    user: $currentUser,
    token: $tokenized['token'],
    encryptedPan: $tokenized['encrypted_pan'],
    last4: $tokenized['last4'],
    brand: $tokenized['brand'],
    expiryMonth: (int) $tokenized['expiry_month'],
    expiryYear: (int) $tokenized['expiry_year'],
    cardholderName: $tokenized['cardholder_name']
);

// Save to database
$entityManager->persist($paymentCard);
$entityManager->flush();
```

### Retrieve and Decrypt Card

```php
// Find card by token
$paymentCard = $entityManager
    ->getRepository(PaymentCard::class)
    ->findOneBy(['token' => 'card_abc123...']);

// Get masked number for display
$masked = $paymentCard->getMaskedNumber(); // "**** **** **** 1111"

// Decrypt full PAN when needed for payment
$encryptedPan = $paymentCard->getEncryptedPan();
$fullPan = $tokenizer->detokenize($encryptedPan);

// Use $fullPan to process payment
// IMPORTANT: Never log or store the decrypted PAN!
```

### Display Stored Cards to User

```php
// Get user's cards
$cards = $entityManager
    ->getRepository(PaymentCard::class)
    ->findBy([
        'user' => $currentUser,
        'status' => 'active'
    ]);

foreach ($cards as $card) {
    echo $card->getBrand() . ' ' . $card->getMaskedNumber() . ' ';
    echo 'Expires: ' . $card->getFormattedExpiry();
    
    if ($card->isExpired()) {
        echo ' (EXPIRED)';
    }
    
    if ($card->isDefault()) {
        echo ' (Default)';
    }
}
```

---

## Security Best Practices

### 1. Encryption Key Management

**Development:**
```dotenv
# .env (not committed)
CARD_ENCRYPTION_KEY="[generated-key]"
```

**Production - Option A: Environment Variables**
```bash
# In your hosting platform
export CARD_ENCRYPTION_KEY="[production-key]"
```

**Production - Option B: Secrets Manager (RECOMMENDED)**
```php
// AWS Secrets Manager
$client = new SecretsManagerClient([/*...*/]);
$result = $client->getSecretValue(['SecretId' => 'prod/card-encryption-key']);
$key = $result['SecretString'];
```

**Production - Option C: Hardware Security Module (BEST)**
- Use AWS KMS, Azure Key Vault, or Google Cloud KMS
- Keys never leave the HSM
- FIPS 140-2 Level 3 compliance

### 2. Key Rotation

Rotate encryption keys annually:

1. Generate new key
2. Keep old key for decryption
3. Decrypt all cards with old key
4. Re-encrypt with new key
5. Update database
6. Retire old key after migration

```php
// Example rotation script
$oldKey = base64_decode($_ENV['OLD_CARD_ENCRYPTION_KEY']);
$newKey = base64_decode($_ENV['CARD_ENCRYPTION_KEY']);

$oldTokenizer = new CardTokenizationService($oldKey);
$newTokenizer = new CardTokenizationService($newKey);

$cards = $repository->findAll();
foreach ($cards as $card) {
    // Decrypt with old key
    $pan = $oldTokenizer->detokenize($card->getEncryptedPan());
    
    // Re-encrypt with new key
    $newEncrypted = $newTokenizer->encrypt($pan);
    
    // Update database
    $card->updateEncryptedPan($newEncrypted);
}
```

### 3. Access Controls

```php
// Only authorized personnel can decrypt
if (!$user->hasRole('ROLE_PCI_ADMIN')) {
    throw new AccessDeniedException('PCI data access denied');
}

// Log all decryption operations
$auditLog->log('card_decrypted', [
    'card_token' => $card->getToken(),
    'user_id' => $currentUser->getId(),
    'ip_address' => $request->getClientIp(),
    'timestamp' => new DateTimeImmutable(),
]);
```

### 4. Never Store CVV

```php
// ❌ WRONG - PCI DSS VIOLATION
$_SESSION['card_cvv'] = '123';

// ✅ CORRECT - CVV only transmitted, never stored
// CVV is sent to payment gateway immediately
// and never persists anywhere
```

### 5. Data Retention

```php
// Delete cards after account closure
$user->close();
foreach ($user->getPaymentCards() as $card) {
    $card->delete(); // Soft delete for audit
}

// Hard delete after retention period (e.g., 7 years for accounting)
$cutoffDate = new DateTimeImmutable('-7 years');
$oldCards = $repository->findDeletedBefore($cutoffDate);
foreach ($oldCards as $card) {
    $entityManager->remove($card); // Hard delete
}
```

---

## PCI DSS Requirements

### What You MUST Do:

1. **Annual Security Audit** ($20k-$50k)
   - Qualified Security Assessor (QSA)
   - Report on Compliance (ROC)
   - Attestation of Compliance (AOC)

2. **Quarterly Vulnerability Scans**
   - Approved Scanning Vendor (ASV)
   - Network penetration testing
   - Application security testing

3. **Secure Network**
   - Firewall configuration
   - No default passwords
   - Encrypted transmission (TLS 1.2+)

4. **Access Controls**
   - Need-to-know basis
   - Unique IDs for each person
   - Multi-factor authentication

5. **Monitoring**
   - All access to card data logged
   - Regular log reviews
   - File integrity monitoring

6. **Security Policies**
   - Written information security policy
   - Risk assessment annually
   - Security awareness training

---

## GDPR Compliance

### Legal Basis

You must have valid legal basis:
- ✅ Contract performance (Art. 6.1.b) - Processing recurring payments
- ✅ Legitimate interest (Art. 6.1.f) - Fraud prevention

### User Rights

Implement these features:

```php
// Right to Access (Art. 15)
public function exportUserCardData(User $user): array
{
    $cards = $repository->findBy(['user' => $user]);
    return array_map(fn($card) => [
        'brand' => $card->getBrand(),
        'last4' => $card->getLast4(),
        'expiry' => $card->getFormattedExpiry(),
        'created' => $card->getCreatedAt(),
        // DO NOT export encrypted PAN
    ], $cards);
}

// Right to Erasure (Art. 17)
public function deleteUserCards(User $user): void
{
    foreach ($user->getPaymentCards() as $card) {
        $entityManager->remove($card);
    }
    $entityManager->flush();
}
```

---

## Alternative: Payment Gateway Tokenization

**RECOMMENDED for most applications:**

### Stripe Example:

```php
// Create customer and payment method (Stripe stores the card)
$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);

$paymentMethod = $stripe->paymentMethods->create([
    'type' => 'card',
    'card' => [
        'number' => '4242424242424242',
        'exp_month' => 12,
        'exp_year' => 2028,
        'cvc' => '123',
    ],
]);

$customer = $stripe->customers->create([
    'email' => 'customer@example.com',
    'payment_method' => $paymentMethod->id,
]);

// Store only Stripe's token (NOT the card)
$user->setStripeCustomerId($customer->id);
$user->setStripePaymentMethodId($paymentMethod->id);

// Later, charge the card using the token
$charge = $stripe->paymentIntents->create([
    'amount' => 5999, // $59.99
    'currency' => 'usd',
    'customer' => $customer->id,
    'payment_method' => $paymentMethod->id,
    'confirm' => true,
]);
```

**Benefits:**
- ✅ No encryption needed
- ✅ No PCI DSS Level 1 audit
- ✅ Stripe handles all compliance
- ✅ Your database never sees card numbers

---

## Cost Comparison

### Self-Hosted Encryption:
- **Setup:** 40-80 hours developer time
- **Annual Audit:** $20,000 - $50,000
- **Quarterly Scans:** $2,000 - $5,000/quarter
- **HSM:** $10,000 - $50,000/year
- **Insurance:** $5,000 - $20,000/year
- **Total Year 1:** ~$50,000 - $150,000

### Payment Gateway Tokenization:
- **Setup:** 4-8 hours developer time
- **Annual Cost:** $0 (included in transaction fees)
- **Compliance:** Handled by gateway
- **Total Year 1:** ~$500 developer time

---

## Testing

```php
// Unit test for tokenization
public function testCardTokenization(): void
{
    $tokenizer = new CardTokenizationService($this->getTestKey());
    
    $cardData = [
        'number' => '4111111111111111',
        'expiry_month' => '12',
        'expiry_year' => '2028',
        'name' => 'Test User',
    ];
    
    $tokenized = $tokenizer->tokenize($cardData);
    
    $this->assertArrayHasKey('token', $tokenized);
    $this->assertArrayHasKey('encrypted_pan', $tokenized);
    $this->assertEquals('1111', $tokenized['last4']);
    $this->assertEquals('Visa', $tokenized['brand']);
    
    // Decrypt and verify
    $decrypted = $tokenizer->detokenize($tokenized['encrypted_pan']);
    $this->assertEquals('4111111111111111', $decrypted);
}

private function getTestKey(): string
{
    return base64_decode('dGVzdC1rZXktMzItYnl0ZXMtZm9yLXVuaXQtdGVzdGluZw==');
}
```

---

## Summary

### ✅ What's Implemented:
1. AES-256-GCM encryption for card data
2. Secure key generation script
3. PaymentCard entity with tokenization
4. Database migration
5. Service registration

### ⚠️ What You Need to Do:
1. **Reconsider** - Use gateway tokenization instead?
2. Generate encryption key
3. Secure key storage (HSM for production)
4. Obtain PCI DSS certification
5. Annual security audits
6. Implement access controls
7. Add audit logging
8. Create key rotation process
9. Update privacy policy
10. Train staff on PCI DSS requirements

### 💰 Budget for:
- Security audits: $20k-$50k/year
- Vulnerability scans: $8k-$20k/year
- HSM or KMS: $10k-$50k/year
- Insurance: $5k-$20k/year
- **Total: $43k-$140k/year**

---

## Recommendation

**Use Stripe, PayPal, or Braintree tokenization** unless you have a specific business requirement that absolutely necessitates storing encrypted cards yourself.

The implementation is here if you need it, but the gateway approach is:
- ✅ Cheaper
- ✅ Safer
- ✅ Easier
- ✅ Less liability

---

**Last Updated:** February 14, 2026  
**Compliance:** PCI DSS v4.0, GDPR (EU 2016/679)

