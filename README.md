[![English](https://img.shields.io/badge/README-English-blue)](README.md)
[![ខ្មែរ](https://img.shields.io/badge/README-ខ្មែរ-green)](README_KM.md)

---

# konthaina/khqr-php

KHQR / EMVCo merchant-presented QR payload generator for PHP (Bakong / Cambodia).
Includes CRC16 (CRC-16/CCITT-FALSE), MD5, and a verification helper.

> Namespace: `Konthaina\Khqr`  
> Main class: `Konthaina\Khqr\KHQRGenerator`

---

## Features

- Generate **KHQR** payload string (EMV Tag-Length-Value format)
- Supports **Individual** and **Merchant** account structures
- Supports **Static QR** and **Dynamic QR**
- Optional fields: amount, bill number, mobile number, store label, terminal label, purpose, alternate language, etc.
- CRC16 calculation + verification
- Returns `md5` hash of the full QR payload string

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

### Generate Dynamic QR (default)
Dynamic QR usually includes `POI=12` and may include timestamp/reference.  
If you set an amount, the QR becomes fixed-amount.

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

---

## Static QR vs Dynamic QR

### Static QR (recommended: no amount)
Static QR should be stable (same string every time).  
In this library, `setStatic(true)` will:
- Set POI (Tag 01) to **11**
- Disable timestamp (Tag 99) for better compatibility

```php
$result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
    ->setStatic(true)
    ->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    // Do NOT setAmount() for static QR
    ->setMerchantCity('Phnom Penh')
    ->generate();

echo $result['qr'] . PHP_EOL;
echo "md5: {$result['md5']}\n";       // stable
echo "verify: " . (KHQRGenerator::verify($result['qr']) ? "OK" : "FAIL") . PHP_EOL;
```

### Dynamic QR (with amount)
Dynamic QR is the default mode (no need to call `setStatic(false)`).

```php
$result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
    ->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    ->setAmount(25.75)
    ->generate();
```

> Note: If you remove amount but keep Dynamic mode (`POI=12`), some scanner apps may treat it as invalid.  
> For “no amount” QR, use **Static** (`setStatic(true)`).

---

## Merchant Type Examples

### Individual (Tag 29)
```php
$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

$result = $khqr->setBakongAccountId('john_smith@devb')
    ->setMerchantName('John Smith')
    ->setAccountInformation('85512233455')     // optional
    ->setAcquiringBank('Dev Bank')             // optional (individual)
    ->setCurrency('USD')
    ->setAmount(5.00)
    ->generate();

echo $result['qr'];
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

echo $result['qr'];
```

---

## Verify KHQR (CRC)

```php
use Konthaina\Khqr\KHQRGenerator;

$isValid = KHQRGenerator::verify($qrString);
```

---

## Returned structure

```php
[
  'qr' => '000201...',
  'timestamp' => '1700000000000', // null for static mode
  'type' => 'individual|merchant',
  'md5' => '...'
]
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

> Note: EMV length uses **byte length**. If you use Khmer/Unicode characters, byte length may differ from character count.

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
git commit -m "Release: v1.0.1"
git tag v1.0.0
git push origin main
git push origin v1.0.1
```

Packagist will pick up tags like `v1.0.1` as stable versions (if webhook enabled).

---

## License

MIT
