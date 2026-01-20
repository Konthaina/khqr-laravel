<?php

require __DIR__ . '/vendor/autoload.php';

use Konthaina\Khqr\KHQRGenerator;

// ===== Individual =====
$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

$result = $khqr->setBakongAccountId('kon_thaina@cadi')
    // ->setStatic(true)
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setAccountInformation('85512233455')
    ->setAcquiringBank('Canadia Bank')
    ->setCurrency('USD')
    ->setAmount(0.01)
    ->setMerchantCity('Phnom Penh')
    ->setBillNumber('#12345')
    ->setMobileNumber('85512233455')
    ->setStoreLabel('Coffee Shop')
    ->setTerminalLabel('Cashier_1')
    ->setPurposeOfTransaction('Coffee')
    ->generate();

echo "=== INDIVIDUAL ===\n";
echo $result['qr'] . "\n";
echo "md5: {$result['md5']}\n";
echo "timestamp: {$result['timestamp']}\n";
echo "verify: " . (KHQRGenerator::verify($result['qr']) ? 'OK' : 'FAIL') . "\n\n";


// ===== Merchant =====
// $khqr2 = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_MERCHANT);

// $result2 = $khqr2->setBakongAccountId('merchant@bank')
//     ->setMerchantId('123456')
//     ->setMerchantName('ABC Store')
//     ->setAcquiringBank('ABC Bank')
//     ->setCurrency('KHR')
//     ->setAmount(50000)
//     ->setMerchantCity('Phnom Penh')
//     ->generate();

// echo "=== MERCHANT ===\n";
// echo $result2['qr'] . "\n";
// echo "timestamp: {$result2['timestamp']}\n";
// echo "verify: " . (KHQRGenerator::verify($result2['qr']) ? 'OK' : 'FAIL') . "\n";
