# PayPal Integration Setup Guide

This guide will help you set up PayPal sandbox for testing payment integration in Neutrino.

## Prerequisites

- Omnipay PayPal package (already installed via composer)
- PayPal Developer Account

## Step 1: Create PayPal Developer Account

1. Go to [PayPal Developer](https://developer.paypal.com/)
2. Sign up or log in with your PayPal account
3. Accept the developer terms and conditions

## Step 2: Create Sandbox Test Accounts

1. Navigate to **Dashboard** > **Sandbox** > **Accounts**
2. PayPal automatically creates two test accounts:
   - **Business Account** (merchant/seller)
   - **Personal Account** (buyer)
3. Note the email addresses for both accounts
4. Click on an account to view credentials (password)

## Step 3: Get API Credentials

### For Express Checkout (Recommended)

1. Go to **Dashboard** > **My Apps & Credentials**
2. Make sure you're in **Sandbox** mode (toggle at top)
3. Under **REST API apps**, click **Create App**
4. Enter an app name (e.g., "Neutrino Checkout")
5. Select your sandbox business account
6. Click **Create App**
7. Copy the **Client ID** and **Secret**

### For Classic API (Alternative)

1. Go to **Dashboard** > **Sandbox** > **Accounts**
2. Click on your **Business Account**
3. Click **API Credentials** tab
4. Under **NVP/SOAP API Integration (Classic)**, click **View/Edit**
5. Copy:
   - **API Username**
   - **API Password**
   - **Signature**

## Step 4: Configure Environment Variables

Copy `.env.dist` to `.env` if you haven't already:

```bash
cp .env.dist .env
```

Edit `.env` and add your PayPal credentials:

```dotenv
# PayPal Configuration
PAYPAL_SANDBOX=true
PAYPAL_API_USERNAME=your-api-username-sb-xxxxx_api1.business.example.com
PAYPAL_API_PASSWORD=your-api-password-1234567890
PAYPAL_API_SIGNATURE=your-api-signature-AbCdEfGhIjKlMnOpQrStUvWxYz
PAYPAL_CLIENT_ID=your-client-id
PAYPAL_SECRET=your-secret
PAYPAL_RETURN_URL=https://www.neutrino.dev:8443/checkout/return
PAYPAL_CANCEL_URL=https://www.neutrino.dev:8443/checkout/cancel
```

### Important Notes:

- Keep `PAYPAL_SANDBOX=true` for testing
- Update return/cancel URLs to match your local development URL
- Never commit `.env` file to version control (it's in `.gitignore`)

## Step 5: Testing the Integration

### Using Sandbox Accounts

1. Start your application
2. Navigate to the checkout page
3. Select a plan and proceed to payment
4. You'll be redirected to PayPal sandbox
5. Log in with your **Personal (buyer) sandbox account**
6. Complete the payment
7. You'll be redirected back to your success page

### Test Credit Cards (Sandbox)

PayPal sandbox also accepts test credit card numbers:

- **Visa**: 4111 1111 1111 1111
- **Mastercard**: 5555 5555 5555 4444
- **Amex**: 3782 822463 10005
- **Discover**: 6011 1111 1111 1117

**CVV**: Any 3-digit number (4 digits for Amex)  
**Expiry**: Any future date

## Step 6: Production Setup

When you're ready to go live:

1. Get production API credentials from PayPal
2. Update `.env`:
   ```dotenv
   PAYPAL_SANDBOX=false
   PAYPAL_API_USERNAME=your-production-username
   PAYPAL_API_PASSWORD=your-production-password
   PAYPAL_API_SIGNATURE=your-production-signature
   PAYPAL_RETURN_URL=https://yourdomain.com/checkout/return
   PAYPAL_CANCEL_URL=https://yourdomain.com/checkout/cancel
   ```
3. Ensure your domain is properly configured
4. Test thoroughly before accepting real payments

## Troubleshooting

### Common Issues

**Error: "Unable to resolve service PayPalService"**
- Clear config cache: `php bin/clear-config-cache.php`
- Restart your application

**Payment redirect not working**
- Check that return/cancel URLs are accessible
- Verify API credentials are correct
- Check error logs for detailed messages

**Transaction failing**
- Ensure sandbox mode is enabled
- Verify test account has sufficient funds (sandbox accounts have virtual money)
- Check PayPal sandbox logs in developer dashboard

### Debugging

Enable detailed logging in `CheckoutProcessHandler.php` and `CheckoutReturnHandler.php` to see full request/response details.

Check logs:
```bash
tail -f /path/to/your/error.log
```

## Payment Flow

1. **Checkout Form** → User fills billing details
2. **Process Handler** → Creates PayPal payment request
3. **PayPal Redirect** → User approves payment on PayPal
4. **Return Handler** → Completes payment and creates subscription
5. **Success Page** → Shows confirmation to user

## Security Best Practices

- Never store credit card information
- Always use HTTPS in production
- Keep API credentials secure
- Validate all incoming data
- Implement proper error handling
- Log all transactions for auditing
- Use webhook notifications for payment status updates

## Additional Resources

- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [Omnipay PayPal Documentation](https://github.com/thephpleague/omnipay-paypal)
- [PayPal Sandbox Guide](https://developer.paypal.com/docs/api-basics/sandbox/)

## Support

For issues related to:
- **PayPal API**: Contact PayPal Developer Support
- **Omnipay Integration**: Check GitHub issues or documentation
- **Neutrino Application**: Contact your development team


