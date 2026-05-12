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

    public function test_dynamic_qr_includes_created_and_expiration_timestamps(): void
    {
        $createdTimestamp = '1755076232336';
        $expirationTimestamp = '1755076292336';

        $result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
            ->setCreatedTimestamp($createdTimestamp)
            ->setExpirationTimestamp($expirationTimestamp)
            ->setBakongAccountId('john_smith@devb')
            ->setMerchantName('John Smith')
            ->generate();

        $this->assertStringContainsString(
            '99340013' . $createdTimestamp . '0113' . $expirationTimestamp,
            $result['qr']
        );
        $this->assertSame($createdTimestamp, $result['timestamp']);
        $this->assertSame($createdTimestamp, $result['createdTimestamp']);
        $this->assertSame($expirationTimestamp, $result['expirationTimestamp']);
        $this->assertTrue(KHQRGenerator::verify($result['qr']));
    }

    public function test_static_qr_includes_created_timestamp_without_expiration_timestamp(): void
    {
        $createdTimestamp = '1755076232336';
        $expirationTimestamp = '1755076292336';

        $result = (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
            ->setStatic(true)
            ->setCreatedTimestamp($createdTimestamp)
            ->setExpirationTimestamp($expirationTimestamp)
            ->setBakongAccountId('john_smith@devb')
            ->setMerchantName('John Smith')
            ->generate();

        $this->assertStringContainsString('99170013' . $createdTimestamp, $result['qr']);
        $this->assertStringNotContainsString('0113' . $expirationTimestamp, $result['qr']);
        $this->assertSame($createdTimestamp, $result['timestamp']);
        $this->assertSame($createdTimestamp, $result['createdTimestamp']);
        $this->assertNull($result['expirationTimestamp']);
        $this->assertTrue(KHQRGenerator::verify($result['qr']));
    }

    public function test_dynamic_expiration_must_be_after_created_timestamp(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration timestamp must be greater than creation timestamp');

        (new KHQRGenerator(KHQRGenerator::MERCHANT_TYPE_INDIVIDUAL))
            ->setCreatedTimestamp('1755076232336')
            ->setExpirationTimestamp('1755076232336')
            ->setBakongAccountId('john_smith@devb')
            ->setMerchantName('John Smith')
            ->generate();
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
