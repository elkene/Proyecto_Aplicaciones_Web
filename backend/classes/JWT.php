<?php

class JWT {
    public static function encode($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            JWT_SECRET,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public static function decode($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        $signature = self::base64UrlDecode($signatureEncoded);

        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            JWT_SECRET,
            true
        );

        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid token signature');
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expired');
        }

        return $payload;
    }

    public static function getTokenFromHeader() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private static function base64UrlEncode($input) {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($input)
        );
    }

    private static function base64UrlDecode($input) {
        $padding = strlen($input) % 4;
        if ($padding) {
            $input .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(
            str_replace(
                ['-', '_'],
                ['+', '/'],
                $input
            )
        );
    }
}
