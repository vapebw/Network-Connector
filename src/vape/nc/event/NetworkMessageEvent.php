<?php

/*
 *  __      __  _     _____   ______ 
 *  \ \    / / / \   |  __ \ |  ____|
 *   \ \  / / / _ \  | |__) || |__   
 *    \ \/ / / _  \ |  ___/ |  __|  
 *     \  / / ___  \| |     | |____ 
 *      \/ /_/   \_\|_|     |______|
 *
 * (c) 2026 vape
 * @author vape
 */

declare(strict_types=1);

namespace vape\nc\event;

use pocketmine\event\Event;

class NetworkMessageEvent extends Event {

    private string $origin = 'unknown';
    private array $data = [];

    public function __construct(
        private string $channel,
        private string $message
    ) {
        $decoded = json_decode($message, true);
        if (is_array($decoded)) {
            $this->origin = $decoded['origin'] ?? 'unknown';
            $this->data = $decoded['data'] ?? [];
        }
    }

    public function getChannel() : string {
        return $this->channel;
    }

    public function getMessage() : string {
        return $this->message;
    }

    public function getOrigin() : string {
        return $this->origin;
    }

    public function getData() : array {
        return $this->data;
    }
}
