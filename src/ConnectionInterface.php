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
     *
     * @param string|array|object $commandOrPayload
     * @param string|array|object|null $payload
     * @throws JsonException
     */
    public function send($commandOrPayload, $payload = null): void;

    /**
     * Close client connection
     */
    public function close(): void;

    /**
     * Unique identifier to this connection
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Get connection instance
     *
     * @return WebSocketConnection
     */
    public function getConnection(): WebSocketConnection;

    /**
     * Store value withing this object
     *
     * @param string $name
     * @param mixed $value
     */
    public function setField(string $name, $value): void;

    /**
     * Gets value stored within this object
     *
     * @param string $name
     * @return mixed
     */
    public function getField(string $name);
}