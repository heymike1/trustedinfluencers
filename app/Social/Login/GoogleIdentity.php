<?php

namespace App\Social\Login;

final class GoogleIdentity
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $avatarUrl = null,
        public readonly bool $emailVerified = true,
    ) {}
}
