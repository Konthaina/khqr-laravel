[English](README.md) | [ខ្មែរ](README_KM.md)
# konthaina/khqr-php

បណ្ណាល័យ PHP សម្រាប់បង្កើត **KHQR / EMVCo (Merchant-Presented QR)** សម្រាប់ Bakong / Cambodia។  
មាន **CRC16 (CRC-16/CCITT-FALSE)**, `md5`, និង function សម្រាប់ verify QR (CRC) ផងដែរ។

> Namespace: `Konthaina\Khqr`  
> Class មេ: `Konthaina\Khqr\KHQRGenerator`

---

## លក្ខណៈពិសេស (Features)

- បង្កើត string **KHQR** (EMV Tag-Length-Value format)
- គាំទ្រ **Individual** និង **Merchant** account structures
- គាំទ្រ **Static QR** និង **Dynamic QR**
- Optional fields៖ amount, bill number, mobile number, store label, terminal label, purpose, alternate language…
- គណនា CRC16 និង verify CRC
- ត្រឡប់ `md5` hash របស់ QR payload string ពេញលេញ

---

## តម្រូវការ (Requirements)

- PHP >= 8.0
- Composer

---

## ដំឡើង (Installation)

### ដំឡើងតាម Composer (Packagist)
```bash
composer require konthaina/khqr-php
```

### ដំឡើងពី local path (សម្រាប់ពេល development)
នៅក្នុង `composer.json` របស់ project អ្នក៖
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

បន្ទាប់មក៖
```bash
composer update
```

---

## ចាប់ផ្តើម (Quick Start)

### Generate Dynamic QR (default)
Dynamic QR ជាទម្លាប់ `POI=12` ហើយអាចមាន timestamp/reference។  
បើអ្នកដាក់ amount វានឹងក្លាយជា fixed-amount QR។

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

## Static QR vs Dynamic QR (ពន្យល់សង្ខេប)

### Static QR (ណែនាំ៖ មិនដាក់ amount)
Static QR គួរតែ “ដដែល” រាល់ពេល generate (stable)។  
ក្នុង library នេះ `setStatic(true)` នឹង៖
- កំណត់ POI (Tag 01) ទៅ **11** (Static)
- បិទ timestamp (Tag 99) សម្រាប់ compatibility ល្អជាង

```php
$result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
    ->setStatic(true)
    ->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    // កុំ setAmount() សម្រាប់ static QR
    ->setMerchantCity('Phnom Penh')
    ->generate();

echo $result['qr'] . PHP_EOL;
echo "md5: {$result['md5']}\n"; // stable
echo "verify: " . (KHQRGenerator::verify($result['qr']) ? "OK" : "FAIL") . PHP_EOL;
```

### Dynamic QR (មាន amount)
Dynamic QR គឺ default (មិនចាំបាច់ហៅ `setStatic(false)` ទេ)។

```php
$result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
    ->setBakongAccountId('kon_thaina@cadi')
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setCurrency('USD')
    ->setAmount(25.75)
    ->generate();
```

> ចំណាំ៖ បើអ្នកដក amount ចេញ តែស្ថិតក្នុង Dynamic mode (`POI=12`) app ខ្លះអាចស្គេនថា invalid។  
> សម្រាប់ “no amount” QR សូមប្រើ **Static** (`setStatic(true)`)។

---

## ឧទាហរណ៍ Merchant Type

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

## រចនាសម្ព័ន្ធទិន្នន័យត្រឡប់ (Returned structure)

```php
[
  'qr' => '000201...',
  'timestamp' => '1700000000000', // static mode នឹងជា null
  'type' => 'individual|merchant',
  'md5' => '...'
]
```

---

## Fields / Limits

Library នេះ truncate fields តាម limit ដែលបានកំណត់ក្នុង code៖

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

> ចំណាំ៖ EMV length ប្រើ **byte length**។ បើប្រើអក្សរខ្មែរ/Unicode ប្រវែង byte អាចខុសពីចំនួនតួអក្សរ។

---

## Development / Testing

ដំឡើង dev dependencies៖
```bash
composer install
```

រត់ test៖
```bash
vendor/bin/phpunit
```

generate autoload៖
```bash
composer dump-autoload
```

---

## Release ទៅ GitHub / Packagist

ពេល update library សូម tag version ថ្មី៖

```bash
git add .
git commit -m "Release: v1.0.1"
git tag v1.0.1
git push origin main
git push origin v1.0.1
```

Packagist នឹងទទួលបាន tag ដូចជា `v1.0.1` ជា stable version (បើ webhook enabled)។

---

## License

MIT
