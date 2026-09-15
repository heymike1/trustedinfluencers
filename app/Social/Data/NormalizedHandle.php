<?php

namespace App\Social\Data;

final class NormalizedHandle
{
    public function __construct(
        public readonly string $handle,
        public readonly string $profileUrl,
        /** Set when the input already carried the canonical provider id (e.g. a /channel/UC… URL). */
        public readonly ?string $providerAccountId = null,
    ) {}
}
