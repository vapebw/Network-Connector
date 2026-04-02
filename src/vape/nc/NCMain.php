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
 * it under the terms of the MIT License (Modified).
 *
 * You are NOT allowed to RESELL this software.
 *
 * @author vape
 */

declare(strict_types=1);

namespace vape\nc;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use vape\nc\manager\RedisManager;

class NCMain extends PluginBase {

    private static self $instance;

    protected function onLoad() : void {
        self::$instance = $this;
    }

    protected function onEnable() : void {
        // TODO: Move credentials to a config.yml in future phases
        RedisManager::init($this, '127.0.0.1', 6379, '');

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void {
            RedisManager::getInstance()->ping(function(bool $isAlive) : void {
                if(!$isAlive) {
                    $this->getLogger()->warning("§c[NC] Redis connection lost! phpredis will try to revive it, but keep an eye on your instance...");
                }
            });
        }), 20 * 5);

        $this->getLogger()->info("§b  __      __  _     _____   ______ ");
        $this->getLogger()->info("§b  \ \    / / / \   |  __ \ |  ____|");
        $this->getLogger()->info("§b   \ \  / / / _ \  | |__) || |__   ");
        $this->getLogger()->info("§b    \ \/ / / _  \ |  ___/ |  __|  ");
        $this->getLogger()->info("§b     \  / / ___  \| |     | |____ ");
        $this->getLogger()->info("§b      \/ /_/   \_\|_|     |______|");
        $this->getLogger()->info("§7     (c) 2026 vape | MIT License (Modified)");
        $this->getLogger()->info("§7     Resale is strictly prohibited.");
        
        $this->getLogger()->info("§b[NC] Network Connector v0.0.1 enabled! Redis is warming up.");
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}
