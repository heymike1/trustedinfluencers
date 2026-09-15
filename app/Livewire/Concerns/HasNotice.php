<?php

namespace App\Livewire\Concerns;

/**
 * Inline feedback for actions that don't redirect. Session flashes only render on the next
 * full page load, so Livewire actions surface their result through this property instead.
 */
trait HasNotice
{
    public ?array $notice = null;

    protected function notify(string $type, string $message): void
    {
        $this->notice = ['type' => $type, 'message' => $message];
    }
}
