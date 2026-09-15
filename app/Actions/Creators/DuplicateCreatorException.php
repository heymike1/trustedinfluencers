<?php

namespace App\Actions\Creators;

use App\Models\Creator;
use RuntimeException;

class DuplicateCreatorException extends RuntimeException
{
    public function __construct(public readonly Creator $existing)
    {
        parent::__construct('This social account is already listed.');
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
