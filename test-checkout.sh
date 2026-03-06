#!/bin/bash

# Test checkout POST endpoint
echo "Testing POST to /checkout/process..."

curl -v -X POST https://www.neutrino.dev:8443/checkout/process \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "plan_id=6dbf0821-9e1b-498b-a989-eccea2e9a1ba" \
  -d "billing_period=monthly" \
  -d "first_name=Test" \
  -d "last_name=User" \
  -d "email=test@example.com" \
  -d "address=123 Test St" \
  -d "city=Test City" \
  -d "zip=12345" \
  -d "country=US" \
  -d "state=CA" \
  -d "card_name=Test User" \
  -d "card_number=4111111111111111" \
  -d "expiry_date=12/28" \
  -d "cvv=123" \
  -d "terms=1" \
  --insecure \
  2>&1 | grep -E "Location:|HTTP/|error"

