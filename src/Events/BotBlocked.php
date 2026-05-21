<?php

namespace Mkopcic\BotProtection\Events;

use Illuminate\Foundation\Events\Dispatchable;

class BotBlocked
{
    use Dispatchable;

    public function __construct(
        /** Puni User-Agent string iz requesta. */
        public readonly string $userAgent,

        /** Client IP adresa. */
        public readonly string $ip,

        /** Full URL koji je bot pokušao otvoriti. */
        public readonly string $url,

        /** Konkretan string iz blocked_agents koji se podudario (ili '(empty)' za prazan UA). */
        public readonly string $matchedAgent,
    ) {}
}
