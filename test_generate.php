<?php

require __DIR__ . '/vendor/autoload.php';

use Konthaina\Khqr\KHQRGenerator;
use Konthaina\Khqr\BakongApiClient;

// ===== Individual =====
$khqr = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);
$client = new BakongApiClient('YOUR_API_KEY_HERE');

$result = $khqr->setBakongAccountId('kon_thaina@cadi')
    // ->setStatic(true)
    ->setMerchantName('Konthaina Co., Ltd.')
    ->setAccountInformation('85512233455')
    ->setAcquiringBank('Canadia Bank')
    ->setCurrency('USD')
    ->setAmount(0.01)
    ->setExpirationDuration(120)
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
echo "createdTimestamp: {$result['createdTimestamp']}\n";
echo "expirationTimestamp: {$result['expirationTimestamp']}\n";
echo "verify: " . (KHQRGenerator::verify($result['qr']) ? 'OK' : 'FAIL') . "\n\n";

// $response = $client->checkTransactionByMd5('07e83c44de651100407212b500f108cb');
// $response = $client->checkTransactionByQr('00020101021229500015kon_thaina@cadi0111855122334550212Canadia Bank52045999530384054040.015802KH5919Konthaina Co., Ltd.6010Phnom Penh62630106#123450211855122334550311Coffee Shop0709Cashier_10806Coffee9917001317708019732156304FC87');
// echo "=== CHECK TRANSACTION BY MD5 ===\n";
// echo json_encode($response, JSON_PRETTY_PRINT) . "\n";




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
