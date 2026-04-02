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
 *
 * This program is free software: you can use it and/or modify
 * it under the terms of the MIT License.
 *
 * @author vape
 */

declare(strict_types=1);

namespace vape\nc\event;

use pocketmine\event\Event;

class NetworkMessageEvent extends Event {

    public function __construct(
        private string $channel,
        private string $message
    ) {}

    public function getChannel() : string {
        return $this->channel;
    }

    public function getMessage() : string {
        return $this->message;
    }
}
