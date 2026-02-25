# konthaina/khqr-php

KHQR / EMVCo merchant-presented QR payload generator for PHP (Bakong / Cambodia).
Includes CRC16 (CRC-16/CCITT-FALSE), MD5, and verification helpers.

> Namespace: `Konthaina\Khqr`
> Main class: `Konthaina\Khqr\KHQRGenerator`

---

## Features

- Generate KHQR payload string (EMV Tag-Length-Value format)
- Supports Individual and Merchant account structures
- Supports Static QR and Dynamic QR
- Optional fields: amount, bill number, mobile number, store label, terminal label, purpose, alternate language, etc.
- CRC16 calculation + verification
- Returns md5 hash of the full QR payload string

---

## Requirements

- PHP >= 8.0
- Composer

---

## Installation

### Install via Composer (Packagist)
```bash
composer require konthaina/khqr-php
```

### Install from local path (during development)
In your main app `composer.json`:
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../khqr-php"
    }
  ],
  "require": {
    "konthaina/khqr-php": "*"
  }
}
```

Then:
```bash
composer update
```

---

## Quick Start

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Konthaina\Khqr\KHQRGenerator;

$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

$result = $khqr->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    ->setAmount(25.75)
    ->setMerchantCity('Phnom Penh')
    ->setBillNumber('#12345')
    ->generate();

echo $result['qr'] . PHP_EOL;
echo "md5: {$result['md5']}\n";
echo "timestamp: {$result['timestamp']}\n";
echo "verify: " . (KHQRGenerator::verify($result['qr']) ? "OK" : "FAIL") . PHP_EOL;
```

Returned structure:
```php
[
  'qr' => '000201...',
  'timestamp' => '1700000000000', // null for static mode
  'type' => 'individual|merchant',
  'md5' => '...'
]
```

---

## Static QR vs Dynamic QR

- Static QR is stable and uses POI=11. It does not include a timestamp.
- Dynamic QR is the default mode (POI=12) and may include a timestamp.
- For a "no amount" QR, use static mode.

Static example:
```php
$result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
    ->setStatic(true)
    ->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    ->setMerchantCity('Phnom Penh')
    ->generate();
```

---

## Merchant Type Examples

### Individual (Tag 29)
```php
$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

$result = $khqr->setBakongAccountId('john_smith@devb')
    ->setMerchantName('John Smith')
    ->setAccountInformation('85512233455')
    ->setAcquiringBank('Dev Bank')
    ->setCurrency('USD')
    ->setAmount(5.00)
    ->generate();
```

### Merchant (Tag 30)
```php
$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_MERCHANT);

$result = $khqr->setBakongAccountId('merchant@bank')
    ->setMerchantId('123456')
    ->setMerchantName('ABC Store')
    ->setAcquiringBank('ABC Bank')
    ->setCurrency('KHR')
    ->setAmount(50000)
    ->generate();
```

---

## Verify KHQR (CRC)

```php
use Konthaina\Khqr\KHQRGenerator;

$isValid = KHQRGenerator::verify($qrString);
```

---

## Verify Transaction by MD5 (Bakong Open API)

Requires a Bakong Open API access token.

```php
use Konthaina\Khqr\BakongApiClient;
use Konthaina\Khqr\KHQRGenerator;

$client = new BakongApiClient('YOUR_ACCESS_TOKEN'); // uses default Bakong base URL

$response = $client->checkTransactionByMd5($result['md5']);
$response = $client->checkTransactionByQr($result['qr']);

$response = KHQRGenerator::checkTransactionByMd5WithToken($result['md5'], 'YOUR_ACCESS_TOKEN');

// Optional: custom base URL
$client = new BakongApiClient('https://api-bakong.nbc.gov.kh', 'YOUR_ACCESS_TOKEN');
```

---

## Renew Access Token (Bakong Open API)

Based on Bakong Open API `POST /v1/renew_token`:

```php
use Konthaina\Khqr\BakongApiClient;
use Konthaina\Khqr\KHQRGenerator;

$response = BakongApiClient::renewToken('your-email@example.com');

// Optional: custom base URL and timeout
$response = BakongApiClient::renewToken('your-email@example.com', 'https://api-bakong.nbc.gov.kh', 30);

// Convenience wrapper
$response = KHQRGenerator::renewBakongToken('your-email@example.com');
```

---

## Verify MD5 Output

Success
```json
{
    "responseCode": 0,
    "responseMessage": "Getting transaction successfully.",
    "errorCode": null,
    "data": {
        "hash": "e40a....",
        "fromAccountId": "developer@cmcb",
        "toAccountId": "developer@devb",
        "currency": "USD",
        "amount": 1.0,
        "description": "",
        "createdDateMs": 1605774370608.0,
        "acknowledgedDateMs": 1605774422421.0
    }
}
```

Not found
```json
{
   "data": null,
   "errorCode": 1,
   "responseCode": 1,
   "responseMessage": "Transaction could not be found. Please check and try again."
}
```

---

## Fields / Limits

The generator truncates fields based on common KHQR limits used in the code:

- Bakong account id: 32
- Merchant name: 25
- Merchant ID: 32
- Acquiring bank: 32
- Account information: 32
- City: 15
- Bill number: 25
- Mobile number: 25
- Store label: 25
- Terminal label: 25
- Purpose: 25
- Language preference: 2
- Merchant name alternate: 25
- City alternate: 15
- UPI account info: 31

Note: EMV length uses byte length. If you use Khmer or Unicode characters, byte length may differ from character count.

---

## Development / Testing

Install dev dependencies:
```bash
composer install
```

Run tests:
```bash
vendor/bin/phpunit
```

Generate autoload:
```bash
composer dump-autoload
```

---

## Release to GitHub / Packagist

Create a new version tag when you update the library:

```bash
git add .
git commit -m "Release: vX.Y.Z"
git tag vX.Y.Z
git push origin main
git push origin vX.Y.Z
```

Packagist will pick up tags like `vX.Y.Z` as stable versions (if webhook enabled).

---

## License

MIT
