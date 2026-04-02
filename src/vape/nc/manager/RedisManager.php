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

namespace vape\nc\manager;

use pocketmine\plugin\PluginBase;
use vape\nc\task\ConnectTask;

/**
 * Singleton Manager for the Redis connection.
 * All traffic goes through here to avoid blocking the Main Thread.
 */
class RedisManager {

    private static ?self $instance = null;

    private PluginBase $plugin;
    private string $host;
    private int $port;
    private string $password;

    private function __construct(PluginBase $plugin, string $host, int $port, string $password = '') {
        $this->plugin = $plugin;
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
    }

    public static function init(PluginBase $plugin, string $host, int $port, string $password = '') : void {
        if (self::$instance === null) {
            self::$instance = new self($plugin, $host, $port, $password);
        }
    }

    public static function getInstance() : self {
        if (self::$instance === null) {
            throw new \RuntimeException("RedisManager is not initialized.");
        }
        return self::$instance;
    }

    /**
     * Generic dispatcher for our Async tasks.
     */
    private function dispatchQuery(int $operation, string $key, string $value, ?callable $onComplete = null) : void {
        $task = new ConnectTask($this->host, $this->port, $this->password, $operation, $key, $value);
        
        if ($onComplete !== null) {
            // Store the closure in the Main Thread scope.
            $task->storeLocal('callback', $onComplete);
        }

        $this->plugin->getServer()->getAsyncPool()->submitTask($task);
    }

    /**
     * Saves a value in Redis asynchronously.
     */
    public function set(string $key, string $value) : void {
        $this->dispatchQuery(ConnectTask::OP_SET, $key, $value);
    }

    /**
     * Gets a value from Redis and executes the closure on the main thread when its done.
     */
    public function get(string $key, callable $onComplete) : void {
        $this->dispatchQuery(ConnectTask::OP_GET, $key, '', $onComplete);
    }

    /**
     * Manual hard-ping. Used by the Heartbeat Task.
     */
    public function ping(callable $onComplete) : void {
        $this->dispatchQuery(ConnectTask::OP_PING, '', '', $onComplete);
    }

    // TODO: Implement Pub/Sub in v0.0.2
}
