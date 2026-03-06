# Fake Payment Gateway - Testing Guide

## Overview

The Fake Payment Gateway is a simulated payment processor that allows you to test the complete checkout flow without needing a real PayPal account or any external payment service credentials.

## Features

✅ **No PayPal Account Required** - Test payments without any API credentials  
✅ **Simulated Payment Page** - Realistic payment flow simulation  
✅ **Success/Fail Testing** - Test both successful and failed payment scenarios  
✅ **Cancel Testing** - Test payment cancellation flow  
✅ **Session-based** - Uses PHP sessions to track payment state  
✅ **Zero Configuration** - Works out of the box

## How It Works

### Payment Flow

1. **Checkout Form** → User fills in billing details and clicks "Place Order"
2. **Process Handler** → Creates a fake payment request with a unique token
3. **Fake Payment Page** → User sees a simulated payment gateway page
4. **User Action** → User can:
   - ✅ Complete Payment (Success)
   - ❌ Fail Payment (Error)
   - ↩️ Cancel Payment
5. **Return Handler** → Processes the result and completes the transaction
6. **Success Page** → Shows confirmation with transaction details

### Technical Details

- **Gateway Class**: `Neutrino\Service\Payment\Fake\FakeGateway`
- **Purchase Request**: `FakePurchaseRequest` - Creates fake payment session
- **Complete Request**: `FakeCompletePurchaseRequest` - Completes the payment
- **Session Storage**: Payment data stored in `$_SESSION['fake_payment']`
- **Transaction IDs**: Generated as `FAKE-TXN-{unique_id}`

## Setup (Already Done!)

The fake gateway is already configured and ready to use:

### Environment Variable

```dotenv
PAYPAL_USE_FAKE=true
```

This is already set in your `.env` file.

### No Credentials Needed

Unlike real PayPal, you don't need:
- API Username
- API Password  
- API Signature
- Client ID
- Secret

Just leave them empty in your `.env` file!

## Testing the Fake Gateway

### Quick Test Steps

1. Visit: `https://www.neutrino.dev:8443/`
2. Select a plan (Monthly or Yearly)
3. Click "Choose Plan"
4. Form is prefilled - click "Place Order"
5. You'll see the **Fake Payment Gateway** page
6. Choose one of three options:
   - **Complete Payment (Success)** - Simulates successful payment
   - **Fail Payment (Error)** - Simulates payment failure
   - **Cancel Payment** - Simulates user cancellation

### Testing Scenarios

#### ✅ Successful Payment
1. Click "Complete Payment (Success)"
2. Redirected to success page
3. Transaction ID displayed (e.g., `FAKE-TXN-67890abcdef`)
4. Subscription details shown with correct billing period

#### ❌ Failed Payment
1. Click "Fail Payment (Error)"
2. Redirected back to checkout
3. Error message: "Payment failed. Please try again..."
4. Can retry payment

#### ↩️ Cancelled Payment
1. Click "Cancel Payment"
2. Redirected back to checkout
3. Warning message: "Payment was cancelled..."
4. Can try again

## Fake Payment Page Features

The fake payment page (`/checkout/fake-payment`) displays:

- **Payment Details**: Description, amount, currency, token
- **Simulated Account**: Shows fake buyer account info
  - Email: `buyer@fake-paypal.test`
  - Balance: USD 10,000.00
- **Action Buttons**: Three clear options for testing
- **Test Mode Alert**: Clearly indicates this is a simulation

## Switching Between Fake and Real PayPal

### Using Fake Gateway (Current Setup)

```dotenv
PAYPAL_USE_FAKE=true
```

Perfect for development and testing without external dependencies.

### Using Real PayPal

To switch to real PayPal sandbox:

```dotenv
PAYPAL_USE_FAKE=false
PAYPAL_SANDBOX=true
PAYPAL_API_USERNAME=your-sandbox-username
PAYPAL_API_PASSWORD=your-sandbox-password
PAYPAL_API_SIGNATURE=your-sandbox-signature
```

### Production Setup

For production with real PayPal:

```dotenv
PAYPAL_USE_FAKE=false
PAYPAL_SANDBOX=false
PAYPAL_API_USERNAME=your-production-username
PAYPAL_API_PASSWORD=your-production-password
PAYPAL_API_SIGNATURE=your-production-signature
```

## Implementation Files

### Gateway Classes
- `src/Neutrino/src/Service/Payment/Fake/FakeGateway.php`
- `src/Neutrino/src/Service/Payment/Fake/FakePurchaseRequest.php`
- `src/Neutrino/src/Service/Payment/Fake/FakePurchaseResponse.php`
- `src/Neutrino/src/Service/Payment/Fake/FakeCompletePurchaseRequest.php`
- `src/Neutrino/src/Service/Payment/Fake/FakeCompletePurchaseResponse.php`

### Handler
- `src/Neutrino/src/Handler/Checkout/FakePaymentHandler.php`
- `src/Neutrino/src/Handler/Checkout/FakePaymentHandlerFactory.php`

### Template
- `src/Neutrino/templates/checkout/fake-payment.phtml`

### Configuration
- `config/autoload/payment.global.php` - Added `use_fake` parameter
- `.env` - Set `PAYPAL_USE_FAKE=true`

## Benefits

### For Development
- ✅ No external API dependencies
- ✅ Works offline
- ✅ Instant feedback
- ✅ No API rate limits
- ✅ No sandbox account needed

### For Testing
- ✅ Test success scenarios
- ✅ Test failure scenarios
- ✅ Test cancellation scenarios
- ✅ Predictable behavior
- ✅ Fast iteration

### For Demos
- ✅ No need to share PayPal credentials
- ✅ Controlled demo flow
- ✅ No external service downtime risk
- ✅ Professional presentation

## Session Data Structure

When a payment is initiated, this data is stored in the session:

```php
$_SESSION['fake_payment'] = [
    'token' => 'FAKE-{unique_id}',
    'amount' => 59.99,
    'currency' => 'USD',
    'description' => 'Pro - Monthly Plan',
    'card' => [...] // Card/billing details
];
```

This data is cleared when payment completes or is cancelled.

## Transaction ID Format

Fake transactions generate IDs in this format:

```
FAKE-TXN-67890abcdef12345
```

This makes it easy to identify test transactions in logs and database.

## Troubleshooting

### Payment page shows "Invalid Payment Session"
- **Cause**: Session was cleared or expired
- **Solution**: Start checkout flow again from plan selection

### Redirect not working
- **Cause**: Routes not configured
- **Solution**: Check that route `/checkout/fake-payment` exists and handler is registered

### Changes not reflecting
- **Solution**: Clear config cache:
  ```bash
  docker exec neutrino_php php bin/clear-config-cache.php
  ```

## Security Notes

⚠️ **Important**: The fake gateway should NEVER be enabled in production!

Always ensure:
```dotenv
PAYPAL_USE_FAKE=false  # In production
```

The fake gateway is purely for development and testing purposes.

## Next Steps

1. ✅ Test the fake payment flow
2. Add database persistence for transactions
3. Implement email notifications
4. When ready, switch to real PayPal sandbox
5. Eventually, configure production PayPal credentials

## Support

For issues or questions:
- Check that `PAYPAL_USE_FAKE=true` in `.env`
- Verify route is registered in `config/routes.php`
- Check handler is registered in `ConfigProvider.php`
- Review session data in browser dev tools
- Check Docker logs for errors

---

**Happy Testing! 🚀**

