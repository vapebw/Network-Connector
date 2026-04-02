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

class NCMain extends PluginBase {

    private static self $instance;

    protected function onLoad() : void {
        self::$instance = $this;
    }

    protected function onEnable() : void {
        if (!class_exists(\Predis\Client::class)) {
            require_once __DIR__ . '/predis/Autoloader.php';
            \Predis\Autoloader::register();
        }

        $this->saveDefaultConfig();
        $config = $this->getConfig();

        $host = $config->getNested('redis.host', '127.0.0.1');
        $port = (int) $config->getNested('redis.port', 6379);
        $password = (string) $config->getNested('redis.password', '');
        $serverId = (string) $config->get('server-id', 'lobby-1');

        RedisManager::init($this, $host, $port, $password, $serverId);

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void {
            RedisManager::getInstance()->ping(function(bool $isAlive) : void {
                if(!$isAlive) {
                    $this->getLogger()->warning("§c[NC] Redis connection lost! phpredis will try to revive it, but keep an eye on your instance...");
                }
            });
        }), 100);

        $this->getScheduler()->scheduleRepeatingTask(new HeartbeatTask(), 300);

        $this->getLogger()->info("§b  __      __  _     _____   ______ ");
        $this->getLogger()->info("§b  \ \    / / / \   |  __ \ |  ____|");
        $this->getLogger()->info("§b   \ \  / / / _ \  | |__) || |__   ");
        $this->getLogger()->info("§b    \ \/ / / _  \ |  ___/ |  __|  ");
        $this->getLogger()->info("§b     \  / / ___  \| |     | |____ ");
        $this->getLogger()->info("§b      \/ /_/   \_\|_|     |______|");
        $this->getLogger()->info("§7     (c) 2026 vape | MIT License");
        
        $this->getLogger()->info("§b[NC] Network Connector v0.0.3 enabled! Redis is warming up.");
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}
