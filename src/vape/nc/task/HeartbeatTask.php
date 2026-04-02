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

namespace vape\nc\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use vape\nc\manager\RedisManager;

class HeartbeatTask extends Task {

    public function onRun() : void {
        $server = Server::getInstance();
        $redis = RedisManager::getInstance();

        $data = [
            'server_name' => $redis->getServerId(),
            'online_players' => count($server->getOnlinePlayers()),
            'max_players' => $server->getMaxPlayers(),
            'tps' => $server->getTicksPerSecond()
        ];

        $redis->broadcast('nc:presence', $data);
    }
}
