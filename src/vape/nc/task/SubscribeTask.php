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
use vape\nc\event\NetworkMessageEvent;

class SubscribeTask extends AsyncTask {

    /**
     * @param string[] $channels
     */
    public function __construct(
        private string $host,
        private int $port,
        private string $password,
        private array $channels
    ) {}

    public function onRun() : void {
        $redis = new Redis();

        try {
            $redis->pconnect($this->host, $this->port, 0.0, 'subscriber_worker');
            $redis->setOption(Redis::OPT_READ_TIMEOUT, -1);
            
            if ($this->password !== '') {
                $redis->auth($this->password);
            }

            $redis->subscribe($this->channels, function(Redis $instance, string $channel, string $message) : void {
                $this->publishProgress([$channel, $message]);
            });

        } catch (RedisException $e) {
        }
    }

    public function onProgressUpdate(mixed $progress) : void {
        $channel = (string) $progress[0];
        $message = (string) $progress[1];

        $event = new NetworkMessageEvent($channel, $message);
        $event->call();
    }
}
