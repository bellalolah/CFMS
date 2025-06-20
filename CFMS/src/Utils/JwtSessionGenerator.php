<?php


namespace Cfms\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JwtSessionGenerator
{
    public static function generate(int $userId, string $email, string $role): string
    {
        $secretKey = getSecretKey();
        $issuer = 'http://localhost:8000';
        $audience = 'http://localhost:3000';
        $tokenLifetime = 60 * 60 * 24; // 24 hours
        $issuedAt = time();
        $expiration = $issuedAt + $tokenLifetime;
        $payload = [
            'iss' => $issuer,
            'aud' => $audience,
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => [
                'id' => $userId,
                'email' => $email,
                'role_id' => $role,
            ]
        ];
        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function decode(string $jwt): object
    {
        $secretKey = getSecretKey();
        return JWT::decode($jwt, new Key($secretKey, 'HS256'));
    }
}
