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

namespace vape\nc\manager;

use pocketmine\plugin\PluginBase;
use vape\nc\task\ConnectTask;
use vape\nc\task\PublishTask;
use vape\nc\task\SubscribeTask;
use vape\nc\utils\NCPayload;

class RedisManager {

    private static ?self $instance = null;

    private PluginBase $plugin;
    private string $host;
    private int $port;
    private string $password;
    private string $serverId;

    private function __construct(PluginBase $plugin, string $host, int $port, string $password = '', string $serverId = 'unknown') {
        $this->plugin = $plugin;
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->serverId = $serverId;
    }

    public static function init(PluginBase $plugin, string $host, int $port, string $password = '', string $serverId = 'unknown') : void {
        if (self::$instance === null) {
            self::$instance = new self($plugin, $host, $port, $password, $serverId);
        }
    }

    public static function getInstance() : self {
        if (self::$instance === null) {
            throw new \RuntimeException("RedisManager is not initialized.");
        }
        return self::$instance;
    }

    public function getServerId() : string {
        return $this->serverId;
    }

    private function dispatchQuery(int $operation, string $key, string $value, ?callable $onComplete = null) : void {
        $task = new ConnectTask($this->host, $this->port, $this->password, $operation, $key, $value);
        
        if ($onComplete !== null) {
            $task->storeLocal('callback', $onComplete);
        }

        $this->plugin->getServer()->getAsyncPool()->submitTask($task);
    }

    public function set(string $key, string $value) : void {
        $this->dispatchQuery(ConnectTask::OP_SET, $key, $value);
    }

    public function get(string $key, callable $onComplete) : void {
        $this->dispatchQuery(ConnectTask::OP_GET, $key, '', $onComplete);
    }

    public function ping(callable $onComplete) : void {
        $this->dispatchQuery(ConnectTask::OP_PING, '', '', $onComplete);
    }

    public function publish(string $channel, string|array $message) : void {
        $task = new PublishTask($this->host, $this->port, $this->password, $channel, $message);
        $this->plugin->getServer()->getAsyncPool()->submitTask($task);
    }

    public function broadcast(string $channel, array $data) : void {
        $payload = new NCPayload($this->serverId, $data, time());
        $this->publish($channel, $payload->jsonSerialize());
    }

    public function subscribe(array $channels) : void {
        $task = new SubscribeTask($this->host, $this->port, $this->password, $channels);
        $this->plugin->getServer()->getAsyncPool()->submitTask($task);
    }
}
