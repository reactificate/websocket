<?php


namespace Reactificate\Websocket;


use Nette\Utils\JsonException;
use Voryx\WebSocketMiddleware\WebSocketConnection;

/**
 * Interface ConnectionInterface
 * @package App\Core\Socket
 */
interface ConnectionInterface
{
    /**
     * Send message to client
     * @param string|array|object $commandOrPayload
     * @param string|array|object|null $payload
     * @return mixed
     * @throws JsonException
     */
    public function send($commandOrPayload, $payload = null);

    /**
     * Close client connection
     * @return mixed
     */
    public function close();

    /**
     * Unique identifier to this connection
     * @return int
     */
    public function getId(): int;

    /**
     * Get connection instance
     * @return WebSocketConnection
     */
    public function getConnection(): WebSocketConnection;
}