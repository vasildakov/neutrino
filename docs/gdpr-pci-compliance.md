# GDPR & PCI DSS Compliance Guide for Payment Data

## Overview

This document outlines what payment data can and cannot be stored under **GDPR** (European Union) and **PCI DSS** (Payment Card Industry Data Security Standard).

---

## ❌ PROHIBITED - Never Store These:

### 1. **Primary Account Number (PAN) - Full Card Number**
- ❌ **Never store unencrypted card numbers**
- ❌ Example: `4111 1111 1111 1111`
- ⚠️ **PCI DSS Level 1 violation**
- ⚠️ **GDPR Art. 32 violation (inadequate security)**

**Exception:** Only if you are PCI DSS Level 1 certified and use:
- Strong encryption (AES-256 or higher)
- Tokenization services
- Hardware Security Modules (HSM)
- Annual security audits

### 2. **CVV/CVC/CVV2/CVC2 Security Codes**
- ❌ **NEVER store under any circumstances**
- ❌ Even encrypted storage is prohibited
- ❌ Example: `123`, `456`
- ⚠️ **PCI DSS Requirement 3.2 - Absolute prohibition**

### 3. **PIN / PIN Blocks**
- ❌ **NEVER store**
- ❌ Even encrypted storage is prohibited

### 4. **Full Magnetic Stripe Data**
- ❌ **NEVER store** track data from card swipes
- ❌ Even encrypted storage is prohibited after authorization

---

## ✅ ALLOWED - Can Store (With Restrictions):

### 1. **First 6 and Last 4 Digits (BIN + Last4)**
- ✅ Can store: `411111******1111`
- ✅ Used for: Card brand identification, dispute resolution
- ⚠️ Must be masked in all displays to users
- **GDPR Basis:** Legitimate interest (Art. 6.1.f)

### 2. **Cardholder Name**
- ✅ Can store: `John Smith`
- ✅ Used for: Transaction verification, receipts
- **GDPR Basis:** Contract performance (Art. 6.1.b)
- ⚠️ Must have data retention policy

### 3. **Expiration Date**
- ✅ Can store: `12/2028`
- ✅ Used for: Recurring payments, card validity checks
- **GDPR Basis:** Contract performance (Art. 6.1.b)
- ⚠️ Delete after card expires + retention period

### 4. **Service Code**
- ✅ Can store: 3-digit code from magnetic stripe
- ✅ Used for: Card type identification
- **PCI DSS:** Allowed if needed for business

### 5. **Payment Token**
- ✅ Can store: `tok_1234567890abcdef`
- ✅ Used for: Reference to payment gateway token
- ✅ Best practice: Use payment processor tokens
- **GDPR Basis:** Legitimate interest (Art. 6.1.f)

---

## ✅ Transaction Metadata (Always Allowed):

### Safe to Store:
```php
[
    'transaction_id'   => 'FAKE-TXN-123456',      // ✅ Safe
    'amount'           => 59.99,                   // ✅ Safe
    'currency'         => 'USD',                   // ✅ Safe
    'description'      => 'Pro Plan - Monthly',    // ✅ Safe
    'status'           => 'completed',             // ✅ Safe
    'timestamp'        => '2026-02-14 10:30:00',   // ✅ Safe
    'ip_address'       => '192.168.1.100',         // ✅ Safe (anonymize after 90 days)
    'user_agent'       => 'Mozilla/5.0...',        // ✅ Safe
    'billing_city'     => 'Sofia',                 // ✅ Safe
    'billing_country'  => 'BG',                    // ✅ Safe
    'payment_method'   => 'card',                  // ✅ Safe
    'card_brand'       => 'Visa',                  // ✅ Safe
    'last4'            => '1111',                  // ✅ Safe
]
```

### ❌ Never Store:
```php
[
    'card_number' => '4111111111111111',  // ❌ PROHIBITED
    'cvv'         => '123',                // ❌ PROHIBITED
    'pin'         => '1234',               // ❌ PROHIBITED
    'track_data'  => '...',                // ❌ PROHIBITED
]
```

---

## GDPR Requirements for Payment Data:

### 1. **Legal Basis (Art. 6 GDPR)**
You must have a valid legal basis:
- **Contract performance** (Art. 6.1.b) - Processing payment
- **Legitimate interest** (Art. 6.1.f) - Fraud prevention, accounting
- **Legal obligation** (Art. 6.1.c) - Tax records, accounting law

### 2. **Data Minimization (Art. 5.1.c)**
- Only collect what's necessary
- Don't store card details if not needed
- Use payment gateway tokens instead

### 3. **Storage Limitation (Art. 5.1.e)**
- Define retention periods
- Delete data when no longer needed
- Example retention:
  - Transaction records: 7-10 years (accounting law)
  - Card details: Delete immediately after payment
  - Logs: 90 days max

### 4. **Security (Art. 32)**
- Encryption at rest and in transit (TLS 1.2+)
- Access controls
- Regular security audits
- Pseudonymization where possible

### 5. **Data Subject Rights**
Users have the right to:
- **Access** their payment data (Art. 15)
- **Rectification** of incorrect data (Art. 16)
- **Erasure** ("right to be forgotten") (Art. 17)
- **Data portability** (Art. 20)

⚠️ **Exception:** You can retain data required by law (e.g., accounting records)

---

## PCI DSS Compliance Levels:

### Level 1: > 6 million transactions/year
- Annual onsite security audit
- Quarterly network scans
- Full compliance required

### Level 2-4: Fewer transactions
- Self-Assessment Questionnaire (SAQ)
- Quarterly network scans

### SAQ A (Recommended for Most Applications):
- Use payment gateway (PayPal, Stripe, etc.)
- No card data touches your servers
- Card data collected via iframe/redirect
- **Easiest compliance path**

---

## Best Practices for Neutrino:

### ✅ Current Implementation (Good):

1. **FakePurchaseRequest.php:**
   ```php
   $_SESSION['fake_payment'] = [
       'token'       => $token,        // ✅ Safe
       'amount'      => $amount,       // ✅ Safe
       'currency'    => $currency,     // ✅ Safe
       'description' => $description,  // ✅ Safe
       // ❌ card data removed - good!
   ];
   ```

2. **CheckoutProcessHandler.php:**
   ```php
   // Only validates card exists, doesn't log actual values
   error_log('cardNumber: ' . ($cardNumber ? 'OK' : 'MISSING')); // ✅ Safe
   ```

3. **Direct redirect to payment gateway:**
   - Card data sent directly to PayPal/Fake gateway
   - Never stored on your servers
   - ✅ SAQ A compliant

### ⚠️ Recommendations:

1. **For Production PayPal:**
   ```php
   // Card data goes directly to PayPal via their API
   // Never store or log the actual values
   $response = $gateway->purchase([
       'amount' => $amount,
       // Card data passed but never stored
   ])->send();
   ```

2. **Store Only Transaction References:**
   ```php
   [
       'transaction_id' => 'PAY-XXX',
       'amount' => 59.99,
       'status' => 'completed',
       'timestamp' => now(),
       // No card data stored
   ]
   ```

3. **Add Data Retention Policy:**
   ```php
   // Delete old transaction data
   // Keep only what's legally required (7 years for accounting)
   ```

4. **Anonymize IP Addresses After 90 Days:**
   ```php
   // Replace last octet: 192.168.1.100 → 192.168.1.0
   ```

---

## Data Retention Policy (Example):

| Data Type | Retention Period | Legal Basis |
|-----------|------------------|-------------|
| Transaction ID | 10 years | Accounting law |
| Amount, Currency | 10 years | Accounting law |
| Card Brand, Last4 | 2 years | Dispute resolution |
| Cardholder Name | 2 years | Dispute resolution |
| IP Address (full) | 90 days | Fraud prevention |
| IP Address (anonymized) | 10 years | Accounting law |
| CVV | 0 seconds | **Never store** |
| Full Card Number | 0 seconds | **Never store** |

---

## Penalties for Non-Compliance:

### GDPR Fines:
- Up to **€20 million** or **4% of global annual revenue** (whichever is higher)
- Examples:
  - British Airways: €22.5 million (2019)
  - Marriott: €20.5 million (2020)

### PCI DSS:
- Fines from card brands: $5,000 - $100,000/month
- Increased transaction fees
- Loss of ability to process cards

---

## Quick Compliance Checklist:

- [ ] ❌ No CVV stored anywhere (code, logs, database, sessions)
- [ ] ❌ No full card numbers stored (unless PCI Level 1 certified)
- [ ] ✅ Use payment gateway tokens instead of card data
- [ ] ✅ Card data transmitted via TLS 1.2+ only
- [ ] ✅ Logs don't contain card numbers or CVV
- [ ] ✅ Error messages don't expose card data
- [ ] ✅ Data retention policy defined and implemented
- [ ] ✅ Privacy policy discloses payment data processing
- [ ] ✅ Users can request data deletion (GDPR Art. 17)
- [ ] ✅ Regular security updates applied

---

## Resources:

- **PCI DSS:** https://www.pcisecuritystandards.org/
- **GDPR:** https://gdpr.eu/
- **GDPR Art. 32:** Security requirements
- **GDPR Art. 5:** Data protection principles
- **PCI DSS Quick Reference:** https://www.pcisecuritystandards.org/documents/PCI_DSS_QRG.pdf

---

## For Neutrino Specifically:

### ✅ What You're Doing Right:
1. Not storing CVV
2. Not storing full card numbers
3. Using payment gateway redirect
4. Logging only validation status, not actual values
5. Removed card data from fake payment session

### 🔧 What to Add for Production:
1. Privacy policy disclosure
2. Data retention policy implementation
3. IP address anonymization after 90 days
4. User data export functionality (GDPR Art. 20)
5. User data deletion functionality (GDPR Art. 17)
6. Regular security audits
7. Encryption for transaction records at rest

---

**Last Updated:** February 14, 2026  
**Compliance Standards:** GDPR (EU 2016/679), PCI DSS v4.0

