<?php

namespace App\Support;

class IdCardToken
{
    /**
     * A stable, keyed token that lets a scanned QR open a public profile
     * without exposing sequential database IDs as the only protection.
     */
    public static function make(string $type, int $id): string
    {
        return substr(hash_hmac('sha256', $type.':'.$id, (string) config('app.key')), 0, 32);
    }

    public static function matches(string $type, int $id, string $token): bool
    {
        return hash_equals(self::make($type, $id), $token);
    }

    public static function url(string $type, int $id): string
    {
        return route('id-cards.profile', [
            'type' => $type,
            'id' => $id,
            'token' => self::make($type, $id),
        ]);
    }
}
