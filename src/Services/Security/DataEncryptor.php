<?php declare(strict_types=1);

namespace Reconmap\Services\Security;

class DataEncryptor
{
    private const string CIPHER_ALGO = 'aes-256-gcm';

    public function encrypt(string $plainText, string $password): array
    {
        $key = $this->deriveKey($password);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_ALGO));
        $cipherText = openssl_encrypt($plainText, self::CIPHER_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);

        return [
            'iv' => $iv,
            'tag' => $tag,
            'cipherText' => $cipherText,
        ];
    }

    public function decrypt(string $cipherText, string $iv, string $password, string $tag): string|false
    {
        $key = $this->deriveKey($password);

        return openssl_decrypt($cipherText, self::CIPHER_ALGO, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }

    private function deriveKey(string $password): string
    {
        return hash('sha256', $password, true);
    }
}
