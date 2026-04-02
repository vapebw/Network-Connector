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

namespace vape\nc\task;

use pocketmine\scheduler\AsyncTask;
use Redis;
use RedisException;

class PublishTask extends AsyncTask {

    public function __construct(
        private string $host,
        private int $port,
        private string $password,
        private string $channel,
        private string $queryMessage
    ) {}

    public function onRun() : void {
        $redis = new Redis();

        try {
            $redis->pconnect($this->host, $this->port, 2.5);
            
            if ($this->password !== '') {
                $redis->auth($this->password);
            }

            $redis->publish($this->channel, $this->queryMessage);

        } catch (RedisException $e) {
        }
    }
}
