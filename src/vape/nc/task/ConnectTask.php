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

            switch ($this->operation) {
                case self::OP_SET:
                    $redis->set($this->queryKey, $this->queryValue);
                    $this->setResult(true);
                    break;

                case self::OP_GET:
                    $result = $redis->get($this->queryKey);
                    $this->setResult($result === null ? null : (string) $result);
                    break;

                case self::OP_PING:
                    $ping = $redis->ping();
                    $isAlive = $ping instanceof \Predis\Response\Status ? $ping->getPayload() === 'PONG' : (bool) $ping;
                    $this->setResult($isAlive);
                    break;
            }

        } catch (PredisException $e) {
            $this->setResult(new \Exception($e->getMessage()));
        }
    }

    public function onCompletion() : void {
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
