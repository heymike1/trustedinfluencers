<?php

namespace App\Enums;

enum ConnectionStatus: string
{
    /** Added by a visitor or admin; the creator has never authenticated. */
    case Unconnected = 'unconnected';
    /** OAuth in progress. */
    case Connecting = 'connecting';
    /** OAuth succeeded, sync jobs queued or running. */
    case Importing = 'importing';
    /** Connected and metrics imported. */
    case Connected = 'connected';
    /** Token expired or revoked; the creator must reconnect. */
    case NeedsReconnection = 'needs_reconnection';
    /** The last sync chain failed. */
    case SyncFailed = 'sync_failed';
    /** The creator disconnected the account. */
    case Disconnected = 'disconnected';

    public function label(): string
    {
        return match ($this) {
            self::Unconnected => 'Not connected',
            self::Connecting => 'Connecting',
            self::Importing => 'Importing data',
            self::Connected => 'Connected',
            self::NeedsReconnection => 'Needs reconnection',
            self::SyncFailed => 'Sync failed',
            self::Disconnected => 'Disconnected',
        };
    }

    /** Whether we hold credentials that may still be usable. */
    public function hasCredentials(): bool
    {
        return in_array($this, [self::Importing, self::Connected, self::SyncFailed], true);
    }
}
