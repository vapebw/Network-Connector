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
        if (!class_exists(Client::class)) {
            require_once dirname(__DIR__) . '/predis/Autoloader.php';
            \Predis\Autoloader::register();
        }

        // We explicitly disable read_write_timeout to prevent the subscriber loop from crashing during inactivity
        $parameters = [
            'scheme' => 'tcp',
            'host'   => $this->host,
            'port'   => $this->port,
            'persistent' => 'subscriber_worker_tunnel',
            'read_write_timeout' => 0
        ];

        if ($this->password !== '') {
            $parameters['password'] = $this->password;
        }

        try {
            $redis = new Client($parameters);
            $pubsub = $redis->pubSubLoop();
            
            // Register channels locally within the Predis consumer
            $pubsub->subscribe($this->channels);

            foreach ($pubsub as $message) {
                if ($message->kind === 'message') {
                    $this->publishProgress([$message->channel, $message->payload]);
                }
            }

        } catch (PredisException $e) {
            // Subscription loop exited. Connection dropped.
        }
    }

    public function onProgressUpdate(mixed $progress) : void {
        $channel = (string) $progress[0];
        $message = (string) $progress[1];

        $event = new NetworkMessageEvent($channel, $message);
        $event->call();
    }
}
