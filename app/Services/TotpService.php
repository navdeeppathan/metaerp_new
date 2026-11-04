<?php

namespace App\Services;

use App\Models\User;

class TotpService
{
    /**
     * Generate a random Base32 secret key.
     */
    public function generateSecret(int $length = 16): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate current 6-digit TOTP code for a secret.
     */
    public function generateCode(string $secret, ?int $timeSlice = null): string
    {
        $timeSlice = $timeSlice ?? (int) floor(time() / 30);
        return $this->calculateCode(strtoupper($secret), $timeSlice);
    }

    /**
     * Validate an entered 6-digit TOTP code against user secret.
     */
    public function validate(User $user, string $code): bool
    {
        if (empty($user->secret) || !preg_match('/^\d{6}$/', trim($code))) {
            return false;
        }

        $code = trim($code);
        $secret = strtoupper($user->secret);
        $timeSlice = (int) floor(time() / 30);

        // Check window of -1, 0, +1 for clock skew
        for ($offset = -1; $offset <= 1; $offset++) {
            if ($this->calculateCode($secret, $timeSlice + $offset) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate OTPAuth URI.
     */
    public function generateQRUrl(User $user): string
    {
        $issuer = 'META-ERP';
        $username = $user->username ?? 'user';
        $secret = $user->secret;

        $otpauth = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($username),
            $secret,
            rawurlencode($issuer)
        );

        return $otpauth;
    }

    private function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        if ($secretKey === false) {
            return '';
        }

        // Pack time into 8-byte big-endian binary string
        $timePacked = pack('N*', 0) . pack('N*', $timeSlice);

        // Hash using HMAC-SHA1
        $hash = hash_hmac('sha1', $timePacked, $secretKey, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $otp = $binary % 1000000;
        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $b32): string|false
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper($b32);
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if ($char === '=' || $char === ' ' || $char === "\n") {
                continue;
            }
            $val = strpos($alphabet, $char);
            if ($val === false) {
                return false;
            }
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
