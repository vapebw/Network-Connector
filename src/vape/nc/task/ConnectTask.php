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

/**
 * Our loyal slave that handles the dirty I/O work in the Worker Threads.
 */
class ConnectTask extends AsyncTask {

    public const OP_SET = 0;
    public const OP_GET = 1;
    public const OP_PING = 2;

    private string $host;
    private int $port;
    private string $password;
    
    private int $operation;
    private string $queryKey;
    private string $queryValue;

    public function __construct(string $host, int $port, string $password, int $operation, string $key, string $value) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        
        $this->operation = $operation;
        $this->queryKey = $key;
        $this->queryValue = $value;
    }

    public function onRun() : void {
        $redis = new Redis();

        try {
            // pconnect securely reuses connections per worker to avoid high connection overhead.
            $redis->pconnect($this->host, $this->port, 2.5);
            
            if ($this->password !== '') {
                $redis->auth($this->password);
            }

            switch ($this->operation) {
                case self::OP_SET:
                    $redis->set($this->queryKey, $this->queryValue);
                    $this->setResult(true);
                    break;

                case self::OP_GET:
                    $result = $redis->get($this->queryKey);
                    $this->setResult($result === false ? null : (string) $result);
                    break;

                case self::OP_PING:
                    $ping = $redis->ping();
                    $this->setResult((bool) $ping);
                    break;
            }

        } catch (RedisException $e) {
            $this->setResult(new \Exception($e->getMessage()));
        }
    }

    public function onCompletion() : void {
        /** @var callable|null $callback */
        $callback = $this->fetchLocal('callback');
        
        if ($callback === null) {
            return;
        }

        $result = $this->getResult();

        if ($result instanceof \Exception) {
            if ($this->operation === self::OP_PING) {
                $callback(false);
            } else {
                $callback(null);
            }
            return;
        }

        $callback($result);
    }
}
