<?php

namespace App\Social\Exceptions;

/**
 * Thrown when the provider rejects our credentials (expired/revoked token).
 * The account is marked "Needs reconnection" and the creator has to re-authenticate.
 */
class ReconnectionRequiredException extends ConnectorException {}
