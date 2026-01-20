# konthaina/khqr-php

KHQR / EMVCo merchant-presented QR payload generator for PHP (Bakong / Cambodia).
Includes CRC16 (CRC-16/CCITT-FALSE) and a simple verification helper.

> Namespace: `Konthaina\Khqr`  
> Main class: `Konthaina\Khqr\KHQRGenerator`

---

## Features

- Generate **KHQR** payload string (EMV tag-length-value format)
- Support **Individual** and **Merchant** account structures
- Optional fields: amount, bill number, mobile number, store label, terminal label, purpose, alternate language, etc.
- CRC16 calculation + verification

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

## Usage (Plain PHP)

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Konthaina\Khqr\KHQRGenerator;

$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

$result = $khqr->setBakongAccountId('john_smith@devb')
    ->setMerchantName('John Smith')
    ->setAccountInformation('85512233455')
    ->setAcquiringBank('Dev Bank')
    ->setCurrency('USD')
    ->setAmount(10.50)
    ->setMerchantCity('Phnom Penh')
    ->setBillNumber('#12345')
    ->setMobileNumber('85512233455')
    ->setStoreLabel('Coffee Shop')
    ->setTerminalLabel('Cashier_1')
    ->setPurposeOfTransaction('Coffee')
    ->generate();

echo $result['qr'] . PHP_EOL;

// Validate CRC
$isValid = KHQRGenerator::verify($result['qr']);
echo $isValid ? "CRC OK\n" : "CRC FAIL\n";
```

Returned structure:
```php
[
  'qr' => '000201...',
  'timestamp' => '1700000000000',
  'type' => 'individual',
  'md5' => '...'
]
```

---

## Usage (Laravel)

If you kept the Laravel integration in the package (`ServiceProvider` + `Facade`), install then use:

### Facade Usage
```php
$result = \KHQR::setBakongAccountId('john_smith@devb')
    ->setMerchantName('John Smith')
    ->setCurrency('USD')
    ->setAmount(1.50)
    ->generate();

return $result['qr'];
```

### Direct Class Usage (always works)
```php
use Konthaina\Khqr\KHQRGenerator;

$khqr = new KHQRGenerator();
$result = $khqr->setBakongAccountId('john_smith@devb')
    ->setMerchantName('John Smith')
    ->generate();
```

> If you are using the Laravel files in a standalone library folder, ensure you have:
```bash
composer require "illuminate/support:^9.0 || ^10.0 || ^11.0"
```

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

> Note: EMV length uses **byte length**. If you put Khmer/Unicode characters, length counting may differ because multibyte characters increase byte count.

---

## Verify KHQR (CRC)

```php
use Konthaina\Khqr\KHQRGenerator;

$isValid = KHQRGenerator::verify($qrString);
```

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

## Release to Packagist

1. Push to GitHub (example):
```bash
git init
git add .
git commit -m "Initial release"
git branch -M main
git remote add origin https://github.com/konthaina/khqr-php.git
git push -u origin main
```

2. Create version tag:
```bash
git tag v1.0.0
git push origin v1.0.0
```

3. Submit repo on Packagist:
- Add your GitHub repository URL
- Enable webhook/auto updates (recommended)

Packagist will pick up tags like `v1.0.0` as stable versions.

---

## License

MIT
