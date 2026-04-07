<?php

namespace App\Services;

class IsbnService
{
    public function normalize(string $isbn): string
    {
        return preg_replace('/\D+/', '', $isbn) ?? '';
    }

    public function isValid13(string $isbn): bool
    {
        $digits = $this->normalize($isbn);
        if (!preg_match('/^(978|979)\d{10}$/', $digits)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $weight = ($i % 2 === 0) ? 1 : 3;
            $sum += ((int) $digits[$i]) * $weight;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $digits[12];
    }

    public function computeCheckDigitFrom12(string $isbn12): int
    {
        $digits = $this->normalize($isbn12);
        if (!preg_match('/^(978|979)\d{9}$/', $digits)) {
            throw new \InvalidArgumentException('Invalid ISBN-12 input');
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $weight = ($i % 2 === 0) ? 1 : 3;
            $sum += ((int) $digits[$i]) * $weight;
        }

        return (10 - ($sum % 10)) % 10;
    }

    public function hyphenate(string $isbn13Digits, string $publisherCode): string
    {
        $digits = $this->normalize($isbn13Digits);
        if (strlen($digits) !== 13) {
            return $digits;
        }

        $prefix = substr($digits, 0, 3);
        $group = substr($digits, 3, 3);
        $pubLen = strlen($publisherCode);
        $publisher = substr($digits, 6, $pubLen);
        $publication = substr($digits, 6 + $pubLen, 12 - (6 + $pubLen));
        $check = substr($digits, -1);

        return implode('-', [$prefix, $group, $publisher, $publication, $check]);
    }
}
