<?php


namespace Reactificate\Websocket;


use InvalidArgumentException;
use Nette\Utils\Json;
use Voryx\WebSocketMiddleware\WebSocketConnection;

class Connection implements ConnectionInterface
{
    protected int $resourceId;

    protected WebSocketConnection $connection;

    protected array $attributes;


    public function __construct(WebsocketConnection $connection)
    {
        $this->connection = $connection;

        $this->resourceId = spl_object_id($connection);
    }

    /**
     * @inheritDoc
     */
    public function send($commandOrPayload, $payload = null): void
    {
        switch (true) {
            case is_array($commandOrPayload):
                $commandOrPayload['time'] = microtime(true);
                $commandOrPayload = Json::encode($commandOrPayload);
                break;
            case is_object($commandOrPayload):
                $commandOrPayload->time = microtime(true);
                $commandOrPayload = Json::encode($commandOrPayload);
                break;
            case is_scalar($commandOrPayload):
                if (null === $payload) {
                    $commandOrPayload = Json::encode([$payload, microtime(true)]);
                } else {
                    $commandOrPayload = Json::encode([
                        'command' => $commandOrPayload,
                        'data' => $payload,
                        'time' => microtime(true)
                    ]);
                }
                break;
            default:
                throw new InvalidArgumentException("Parameter 1 must be of type scalar|array|object");
        }

        $this->connection->send($commandOrPayload);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->resourceId;
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): WebSocketConnection
    {
        return $this->connection;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setField(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getField(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
}