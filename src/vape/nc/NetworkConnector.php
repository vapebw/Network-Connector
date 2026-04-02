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

namespace vape\nc;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use vape\nc\manager\RedisManager;
use vape\nc\task\HeartbeatTask;

class NetworkConnector {

    public static function setup(PluginBase $owner, array $config) : void {
        if (!class_exists(\Predis\Client::class)) {
            require_once __DIR__ . '/predis/Autoloader.php';
            \Predis\Autoloader::register();
        }

        $host = $config['redis']['host'] ?? '127.0.0.1';
        $port = (int) ($config['redis']['port'] ?? 6379);
        $password = (string) ($config['redis']['password'] ?? '');
        $serverId = (string) ($config['server-id'] ?? 'lobby-1');

        RedisManager::init($owner, $host, $port, $password, $serverId);

        $owner->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use ($owner) : void {
            RedisManager::getInstance()->ping(function(bool $isAlive) use ($owner) : void {
                if(!$isAlive) {
                    $owner->getLogger()->warning("§c[NC] Redis connection lost! phpredis will try to revive it, but keep an eye on your instance...");
                }
            });
        }), 100);

        $owner->getScheduler()->scheduleRepeatingTask(new HeartbeatTask(), 300);
        
        $owner->getLogger()->info("§b[NC] Network Connector v0.0.3 initialized!");
    }

}
