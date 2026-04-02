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

namespace vape\nc\utils;

use JsonSerializable;

readonly class NCPayload implements JsonSerializable {

    public function __construct(
        private string $origin,
        private mixed $data,
        private int $timestamp
    ) {}

    public function getOrigin() : string {
        return $this->origin;
    }

    public function getData() : mixed {
        return $this->data;
    }

    public function getTimestamp() : int {
        return $this->timestamp;
    }

    public function jsonSerialize() : array {
        return [
            'origin' => $this->origin,
            'timestamp' => $this->timestamp,
            'data' => $this->data
        ];
    }
}
