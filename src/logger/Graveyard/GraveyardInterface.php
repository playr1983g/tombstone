<?php

declare(strict_types=1);

namespace Scheb\Tombstone\Logger\Graveyard;

interface GraveyardInterface
{
    public function logTombstoneCall(array $arguments, array $trace, array $metadata): void;

    public function flush(): void;
}
