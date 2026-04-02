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
use Predis\Client;
use Predis\PredisException;

class PublishTask extends AsyncTask {

    public function __construct(
        private string $host,
        private int $port,
        private string $password,
        private string $channel,
        private string $queryMessage
    ) {}

    public function onRun() : void {
        if (!class_exists(Client::class)) {
            require_once dirname(__DIR__) . '/predis/Autoloader.php';
            \Predis\Autoloader::register();
        }

        $parameters = [
            'scheme' => 'tcp',
            'host'   => $this->host,
            'port'   => $this->port,
            'persistent' => true
        ];

        if ($this->password !== '') {
            $parameters['password'] = $this->password;
        }

        try {
            $redis = new Client($parameters);
            $redis->publish($this->channel, $this->queryMessage);
        } catch (PredisException $e) {
            // Silently fail if connection drops during broadcast
        }
    }
}
