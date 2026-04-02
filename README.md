# Network Connector (NC) - Asynchronous Redis Library

> [!WARNING]  
> **This project is currently under active development and is NOT stable.**  
> Features may not work as expected and the API is subject to change. Use at your own risk.

**Network Connector** is a high-performance, non-blocking Redis library for PocketMine-MP 5 (PHP 8.2+). It utilizes the `phpredis` extension to provide stable, asynchronous communication between the Minecraft server and a Redis instance, preventing TPS drops during heavy I/O operations.

## Features (v0.0.2)
- **Asynchronous Engine**: Powered by `AsyncTask` and `phpredis` for maximum throughput.
- **Persistent Connections**: Uses `pconnect` to reuse connections across worker threads, minimizing handshake overhead.
- **Unified Manager**: Singleton `RedisManager` for easy integration and thread-safe callback handling.
- **Heartbeat System**: Automatic monitoring of the Redis connection status.
- **Pub/Sub (WIP)**: Listen and publish to isolated messaging channels asynchronously globally.

## Future Phases
The library is designed to evolve into a full-scale network communication layer for distributed systems:

- **Phase 0.0.2 - Pub/Sub Implementation**: 
  - Add native support for publishing and subscribing to Redis channels.
  - Dedicated worker threads for constant message listening without blocking the main thread.
- **Phase 0.0.3 - Data Serialization Protocols**: 
  - Implementation of fast serialization (MsgPack/JSON) for cross-server data syncing.
- **Phase 0.1.0 - Network State & Sync**: 
  - Real-time player status tracking and global command synchronization across multiple server instances.

## License
This project is licensed under the **MIT License**.

---
Developed by **vape**.
