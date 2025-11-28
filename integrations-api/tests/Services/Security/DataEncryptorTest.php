<?php declare(strict_types=1);

namespace Reconmap\Services\Security;

use PHPUnit\Framework\TestCase;

class DataEncryptorTest extends TestCase
{
    public function testDecryptionWithCorrectPassword()
    {
        $plainText = 'Something I want to encrypt.';
        $password = 'THE SECRET';

        $subject = new DataEncryptor();
        $encrypted = $subject->encrypt($plainText, $password);

        $decryptedText = $subject->decrypt($encrypted['cipherText'], $encrypted['iv'], $password, $encrypted['tag']);

        $this->assertEquals($plainText, $decryptedText);
    }

    public function testDecryptionWithIncorrectPassword()
    {
        $plainText = 'Something I want to encrypt.';
        $password = 'THE SECRET';
        $wrongPassword = 'I DUNNO the secret';

        $subject = new DataEncryptor();
        $encrypted = $subject->encrypt($plainText, $password);

        $decryptedText = $subject->decrypt($encrypted['cipherText'], $encrypted['iv'], $wrongPassword, $encrypted['tag']);

        $this->assertFalse($decryptedText);
    }
}
