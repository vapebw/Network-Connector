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

use pocketmine\scheduler\AsyncTask;
use Predis\Client;
use Predis\PredisException;

class PublishTask extends AsyncTask {

    public function __construct(
        private string $host,
        private int $port,
        private string $password,
        private string $channel,
        private string|array $queryMessage
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
            $message = is_array($this->queryMessage) ? json_encode($this->queryMessage, JSON_THROW_ON_ERROR) : $this->queryMessage;
            $redis->publish($this->channel, $message);
        } catch (\Exception) {
        }
    }
}
