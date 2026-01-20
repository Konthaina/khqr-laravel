<?php

declare(strict_types=1);

namespace Konthaina\Khqr\Tests;

use Konthaina\Khqr\KHQRGenerator;
use PHPUnit\Framework\TestCase;

final class KHQRGeneratorTest extends TestCase
{
    public function test_generate_and_verify(): void
    {
        $gen = new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL);

        $result = $gen->setBakongAccountId('john_smith@devb')
            ->setMerchantName('John Smith')
            ->setCurrency('USD')
            ->setAmount(10.50)
            ->setMerchantCity('Phnom Penh')
            ->generate();

        $this->assertArrayHasKey('qr', $result);
        $this->assertTrue(KHQRGenerator::verify($result['qr']));
    }

    public function test_verify_fails_when_modified(): void
    {
        $gen = new KHQRGenerator();
        $result = $gen->setBakongAccountId('john_smith@devb')
            ->setMerchantName('John Smith')
            ->generate();

        $qr = $result['qr'];

        // change one character (before CRC)
        $modified = substr($qr, 0, 10) . 'X' . substr($qr, 11);

        $this->assertFalse(KHQRGenerator::verify($modified));
    }
}
