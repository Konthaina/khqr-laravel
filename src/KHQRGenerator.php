<?php

declare(strict_types=1);

namespace Konthaina\Khqr;

/**
 * KHQR Generator Library
 * Based on National Bank of Cambodia KHQR Specification v2.7
 */
class KHQRGenerator
{
    public const CURRENCY_KHR = '116';
    public const CURRENCY_USD = '840';

    public const MERCHANT_TYPE_INDIVIDUAL = 'individual';
    public const MERCHANT_TYPE_MERCHANT = 'merchant';

    private array $data = [];
    private string $merchantType;

    public function __construct(string $merchantType = self::MERCHANT_TYPE_INDIVIDUAL)
    {
        $this->merchantType = $merchantType;
    }

    public function setBakongAccountId(string $accountId): self
    {
        $this->data['bakongAccountId'] = substr($accountId, 0, 32);
        return $this;
    }

    public function setMerchantName(string $name): self
    {
        $this->data['merchantName'] = substr($name, 0, 25);
        return $this;
    }

    public function setMerchantId(string $merchantId): self
    {
        $this->data['merchantId'] = substr($merchantId, 0, 32);
        return $this;
    }

    public function setAcquiringBank(string $bank): self
    {
        $this->data['acquiringBank'] = substr($bank, 0, 32);
        return $this;
    }

    public function setAccountInformation(string $info): self
    {
        $this->data['accountInformation'] = substr($info, 0, 32);
        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->data['currency'] = strtoupper($currency) === 'USD' ? self::CURRENCY_USD : self::CURRENCY_KHR;
        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->data['amount'] = number_format($amount, 2, '.', '');
        return $this;
    }

    public function setMerchantCity(string $city): self
    {
        $this->data['merchantCity'] = substr($city, 0, 15);
        return $this;
    }

    public function setBillNumber(string $billNumber): self
    {
        $this->data['billNumber'] = substr($billNumber, 0, 25);
        return $this;
    }

    public function setMobileNumber(string $mobile): self
    {
        $this->data['mobileNumber'] = substr($mobile, 0, 25);
        return $this;
    }

    public function setStoreLabel(string $label): self
    {
        $this->data['storeLabel'] = substr($label, 0, 25);
        return $this;
    }

    public function setTerminalLabel(string $label): self
    {
        $this->data['terminalLabel'] = substr($label, 0, 25);
        return $this;
    }

    public function setPurposeOfTransaction(string $purpose): self
    {
        $this->data['purposeOfTransaction'] = substr($purpose, 0, 25);
        return $this;
    }

    public function setUpiAccountInformation(string $upi): self
    {
        $this->data['upiAccountInformation'] = substr($upi, 0, 31);
        return $this;
    }

    public function setLanguagePreference(string $lang): self
    {
        $this->data['languagePreference'] = substr($lang, 0, 2);
        return $this;
    }

    public function setMerchantNameAlternate(string $name): self
    {
        $this->data['merchantNameAlternate'] = substr($name, 0, 25);
        return $this;
    }

    public function setMerchantCityAlternate(string $city): self
    {
        $this->data['merchantCityAlternate'] = substr($city, 0, 15);
        return $this;
    }

    /**
     * Generate KHQR String
     */
    public function generate(): array
    {
        if (empty($this->data['bakongAccountId']) || empty($this->data['merchantName'])) {
            throw new \InvalidArgumentException('Bakong Account ID and Merchant Name are required');
        }

        if ($this->merchantType === self::MERCHANT_TYPE_MERCHANT) {
            if (empty($this->data['merchantId']) || empty($this->data['acquiringBank'])) {
                throw new \InvalidArgumentException('Merchant ID and Acquiring Bank are required for merchant type');
            }
        }

        $qr = '';

        // Payload Format Indicator (Tag 00)
        $qr .= $this->formatTag('00', '01');

        // Point of Initiation Method (Tag 01) - keep as your original ("12")
        $qr .= $this->formatTag('01', '12');

        // UPI Merchant Account (Tag 15) - Optional
        if (!empty($this->data['upiAccountInformation'])) {
            $qr .= $this->formatTag('15', $this->data['upiAccountInformation']);
        }

        // Account Information
        if ($this->merchantType === self::MERCHANT_TYPE_INDIVIDUAL) {
            // Individual Account (Tag 29)
            $tag29 = $this->formatTag('00', $this->data['bakongAccountId']);

            if (!empty($this->data['accountInformation'])) {
                $tag29 .= $this->formatTag('01', $this->data['accountInformation']);
            }
            if (!empty($this->data['acquiringBank'])) {
                $tag29 .= $this->formatTag('02', $this->data['acquiringBank']);
            }

            $qr .= $this->formatTag('29', $tag29);
        } else {
            // Merchant Account (Tag 30)
            $tag30 = $this->formatTag('00', $this->data['bakongAccountId']);
            $tag30 .= $this->formatTag('01', $this->data['merchantId']);
            $tag30 .= $this->formatTag('02', $this->data['acquiringBank']);
            $qr .= $this->formatTag('30', $tag30);
        }

        // Merchant Category Code (Tag 52)
        $qr .= $this->formatTag('52', '5999');

        // Transaction Currency (Tag 53)
        $currency = $this->data['currency'] ?? self::CURRENCY_KHR;
        $qr .= $this->formatTag('53', $currency);

        // Transaction Amount (Tag 54) - Optional
        if (!empty($this->data['amount'])) {
            $qr .= $this->formatTag('54', $this->data['amount']);
        }

        // Country Code (Tag 58)
        $qr .= $this->formatTag('58', 'KH');

        // Merchant Name (Tag 59)
        $qr .= $this->formatTag('59', $this->data['merchantName']);

        // Merchant City (Tag 60)
        $city = $this->data['merchantCity'] ?? 'Phnom Penh';
        $qr .= $this->formatTag('60', $city);

        // Additional Data Field (Tag 62)
        $tag62 = '';
        if (!empty($this->data['billNumber'])) {
            $tag62 .= $this->formatTag('01', $this->data['billNumber']);
        }
        if (!empty($this->data['mobileNumber'])) {
            $tag62 .= $this->formatTag('02', $this->data['mobileNumber']);
        }
        if (!empty($this->data['storeLabel'])) {
            $tag62 .= $this->formatTag('03', $this->data['storeLabel']);
        }
        if (!empty($this->data['terminalLabel'])) {
            $tag62 .= $this->formatTag('07', $this->data['terminalLabel']);
        }
        if (!empty($this->data['purposeOfTransaction'])) {
            $tag62 .= $this->formatTag('08', $this->data['purposeOfTransaction']);
        }
        if (!empty($tag62)) {
            $qr .= $this->formatTag('62', $tag62);
        }

        // Merchant Alternate Language (Tag 64)
        $tag64 = '';
        if (!empty($this->data['languagePreference'])) {
            $tag64 .= $this->formatTag('00', $this->data['languagePreference']);
        }
        if (!empty($this->data['merchantNameAlternate'])) {
            $tag64 .= $this->formatTag('01', $this->data['merchantNameAlternate']);
        }
        if (!empty($this->data['merchantCityAlternate'])) {
            $tag64 .= $this->formatTag('02', $this->data['merchantCityAlternate']);
        }
        if (!empty($tag64)) {
            $qr .= $this->formatTag('64', $tag64);
        }

        // Timestamp (Tag 99) - store as integer milliseconds string
        $timestamp = (string) ((int) round(microtime(true) * 1000));
        $qr .= $this->formatTag('99', $this->formatTag('00', $timestamp));

        // CRC (Tag 63)
        $crc = $this->calculateCRC($qr . '6304');
        $qr .= '6304' . $crc;

        return [
            'qr' => $qr,
            'timestamp' => $timestamp,
            'type' => $this->merchantType,
            'md5' => md5($qr),
        ];
    }

    /**
     * Format EMV tag
     */
    private function formatTag(string $tag, string $value): string
    {
        if ($value === '') {
            return '';
        }

        $len = strlen($value);
        if ($len > 99) {
            // EMV length field is 2 digits (00-99)
            throw new \InvalidArgumentException("Value too long for tag {$tag}: {$len} bytes");
        }

        $length = str_pad((string) $len, 2, '0', STR_PAD_LEFT);
        return $tag . $length . $value;
    }

    /**
     * Calculate CRC-16/CCITT-FALSE
     */
    private function calculateCRC(string $data): string
    {
        $crc16tab = [
            0x0000,
            0x1021,
            0x2042,
            0x3063,
            0x4084,
            0x50a5,
            0x60c6,
            0x70e7,
            0x8108,
            0x9129,
            0xa14a,
            0xb16b,
            0xc18c,
            0xd1ad,
            0xe1ce,
            0xf1ef,
            0x1231,
            0x0210,
            0x3273,
            0x2252,
            0x52b5,
            0x4294,
            0x72f7,
            0x62d6,
            0x9339,
            0x8318,
            0xb37b,
            0xa35a,
            0xd3bd,
            0xc39c,
            0xf3ff,
            0xe3de,
            0x2462,
            0x3443,
            0x0420,
            0x1401,
            0x64e6,
            0x74c7,
            0x44a4,
            0x5485,
            0xa56a,
            0xb54b,
            0x8528,
            0x9509,
            0xe5ee,
            0xf5cf,
            0xc5ac,
            0xd58d,
            0x3653,
            0x2672,
            0x1611,
            0x0630,
            0x76d7,
            0x66f6,
            0x5695,
            0x46b4,
            0xb75b,
            0xa77a,
            0x9719,
            0x8738,
            0xf7df,
            0xe7fe,
            0xd79d,
            0xc7bc,
            0x48c4,
            0x58e5,
            0x6886,
            0x78a7,
            0x0840,
            0x1861,
            0x2802,
            0x3823,
            0xc9cc,
            0xd9ed,
            0xe98e,
            0xf9af,
            0x8948,
            0x9969,
            0xa90a,
            0xb92b,
            0x5af5,
            0x4ad4,
            0x7ab7,
            0x6a96,
            0x1a71,
            0x0a50,
            0x3a33,
            0x2a12,
            0xdbfd,
            0xcbdc,
            0xfbbf,
            0xeb9e,
            0x9b79,
            0x8b58,
            0xbb3b,
            0xab1a,
            0x6ca6,
            0x7c87,
            0x4ce4,
            0x5cc5,
            0x2c22,
            0x3c03,
            0x0c60,
            0x1c41,
            0xedae,
            0xfd8f,
            0xcdec,
            0xddcd,
            0xad2a,
            0xbd0b,
            0x8d68,
            0x9d49,
            0x7e97,
            0x6eb6,
            0x5ed5,
            0x4ef4,
            0x3e13,
            0x2e32,
            0x1e51,
            0x0e70,
            0xff9f,
            0xefbe,
            0xdfdd,
            0xcffc,
            0xbf1b,
            0xaf3a,
            0x9f59,
            0x8f78,
            0x9188,
            0x81a9,
            0xb1ca,
            0xa1eb,
            0xd10c,
            0xc12d,
            0xf14e,
            0xe16f,
            0x1080,
            0x00a1,
            0x30c2,
            0x20e3,
            0x5004,
            0x4025,
            0x7046,
            0x6067,
            0x83b9,
            0x9398,
            0xa3fb,
            0xb3da,
            0xc33d,
            0xd31c,
            0xe37f,
            0xf35e,
            0x02b1,
            0x1290,
            0x22f3,
            0x32d2,
            0x4235,
            0x5214,
            0x6277,
            0x7256,
            0xb5ea,
            0xa5cb,
            0x95a8,
            0x8589,
            0xf56e,
            0xe54f,
            0xd52c,
            0xc50d,
            0x34e2,
            0x24c3,
            0x14a0,
            0x0481,
            0x7466,
            0x6447,
            0x5424,
            0x4405,
            0xa7db,
            0xb7fa,
            0x8799,
            0x97b8,
            0xe75f,
            0xf77e,
            0xc71d,
            0xd73c,
            0x26d3,
            0x36f2,
            0x0691,
            0x16b0,
            0x6657,
            0x7676,
            0x4615,
            0x5634,
            0xd94c,
            0xc96d,
            0xf90e,
            0xe92f,
            0x99c8,
            0x89e9,
            0xb98a,
            0xa9ab,
            0x5844,
            0x4865,
            0x7806,
            0x6827,
            0x18c0,
            0x08e1,
            0x3882,
            0x28a3,
            0xcb7d,
            0xdb5c,
            0xeb3f,
            0xfb1e,
            0x8bf9,
            0x9bd8,
            0xabbb,
            0xbb9a,
            0x4a75,
            0x5a54,
            0x6a37,
            0x7a16,
            0x0af1,
            0x1ad0,
            0x2ab3,
            0x3a92,
            0xfd2e,
            0xed0f,
            0xdd6c,
            0xcd4d,
            0xbdaa,
            0xad8b,
            0x9de8,
            0x8dc9,
            0x7c26,
            0x6c07,
            0x5c64,
            0x4c45,
            0x3ca2,
            0x2c83,
            0x1ce0,
            0x0cc1,
            0xef1f,
            0xff3e,
            0xcf5d,
            0xdf7c,
            0xaf9b,
            0xbfba,
            0x8fd9,
            0x9ff8,
            0x6e17,
            0x7e36,
            0x4e55,
            0x5e74,
            0x2e93,
            0x3eb2,
            0x0ed1,
            0x1ef0
        ];

        $crc = 0xFFFF;
        $length = strlen($data);

        for ($i = 0; $i < $length; $i++) {
            $c = ord($data[$i]);
            $crc = (($crc << 8) ^ $crc16tab[(($crc >> 8) ^ $c) & 0xFF]) & 0xFFFF;
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Verify KHQR string
     */
    public static function verify(string $qr): bool
    {
        // Must at least contain ...6304XXXX
        if (strlen($qr) < 12) {
            return false;
        }

        // Must end with "6304" + 4 hex
        $crcTag = substr($qr, -8, 4);
        if ($crcTag !== '6304') {
            return false;
        }

        $extractedCrc = substr($qr, -4);
        $qrWithoutCrc = substr($qr, 0, -4); // includes "6304"

        $generator = new self();
        $calculatedCrc = $generator->calculateCRC($qrWithoutCrc);

        return strtoupper($extractedCrc) === strtoupper($calculatedCrc);
    }
}
