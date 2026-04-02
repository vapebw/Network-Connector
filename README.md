# Network Connector (NC) - Asynchronous Redis Library

> [!WARNING]  
> **This project is currently under active development and is NOT stable.**  
> Features may not work as expected and the API is subject to change. Use at your own risk.

**Network Connector** is a high-performance, non-blocking Redis library for PocketMine-MP 5 (PHP 8.2+). It utilizes the pure PHP `Predis` library to provide stable, asynchronous communication between the Minecraft server and a Redis instance, preventing TPS drops during heavy I/O operations without depending on external binaries.

## Setup (Virion)
To use this library, shade it into your plugin and initialize it in your `onEnable()`:

```php
use vape\nc\NetworkConnector;

protected function onEnable() : void {
    NetworkConnector::setup($this, [
        "server-id" => "lobby-1",
        "redis" => [
            "host" => "127.0.0.1",
            "port" => 6379,
            "password" => ""
        ]
    ]);
}
```

## Features (v0.0.3)
- **Asynchronous Engine**: Powered by `AsyncTask` and `Predis` for maximum throughput.
- **Library Format**: Pure Virion (no plugin.yml) for easy integration.
- **Server Identity**: Track message origins across your network.
- **Auto Serialization**: Automatic `json_encode` for arrays.
- **Presence Heartbeat**: Global status reporting to `nc:presence`.

## Usage

### Broadcasting Data
```php
RedisManager::getInstance()->broadcast("nc:updates", [
    "action" => "update_rank",
    "player" => "vape",
    "rank" => "Admin"
]);
```

### Receiving Data
```php
public function onNetworkMessage(NetworkMessageEvent $event) : void {
    $origin = $event->getOrigin(); // "lobby-1"
    $data = $event->getData(); // ["action" => "update_rank", ...]
}
```

## Future Phases
- **Phase 0.1.0 - Network State & Sync**: 
  - Real-time player status tracking and global command synchronization across multiple server instances.

## License
This project is licensed under the **MIT License**.

---
Developed by **vape**.
