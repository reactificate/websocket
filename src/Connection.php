<?php


namespace Reactify\Websocket;


use InvalidArgumentException;
use Nette\Utils\Json;
use Reactify\Console;
use Voryx\WebSocketMiddleware\WebSocketConnection;

class Connection implements ConnectionInterface
{
    protected int $resourceId;

    protected WebSocketConnection $connection;

    public function __construct(WebsocketConnection $connection)
    {
        $this->connection = $connection;

        $this->resourceId = spl_object_id($connection);
    }

    /**
     * @inheritDoc
     */
    public function send($commandOrPayload, $payload = null)
    {
        switch (true){
            case is_array($commandOrPayload):
                $commandOrPayload['time'] = microtime(true);
                $commandOrPayload = Json::encode($commandOrPayload);
                break;
            case is_object($commandOrPayload):
                $commandOrPayload->time = microtime(true);
                $commandOrPayload = Json::encode($commandOrPayload);
                break;
            case is_scalar($commandOrPayload):
                if(null !== $payload){
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
    public function close()
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
}