<?php


namespace Reactificate\Websocket;


use Nette\Utils\JsonException;
use Ratchet\RFC6455\Messaging\Message;
use Reactificate\Websocket\Exceptions\InvalidPayloadException;
use Throwable;
use Voryx\WebSocketMiddleware\WebSocketConnection;
use Voryx\WebSocketMiddleware\WebSocketMiddleware;

class Middleware
{
    protected WebSocketHandlerInterface $handler;


    /**
     * @param WebSocketHandlerInterface ...$webSocketHandlers
     * @return WebSocketMiddleware[]
     */
    public static function create(WebSocketHandlerInterface ...$webSocketHandlers): array
    {
        $wsMiddleware = [];

        foreach ($webSocketHandlers as $webSocketHandler) {
            $wsMiddleware[] = new WebSocketMiddleware(
                [$webSocketHandler->getServerInfo()->getPrefix()],
                new Middleware($webSocketHandler)
            );
        }

        return $wsMiddleware;
    }

    public function __construct(WebSocketHandlerInterface $webSocketHandler)
    {
        $this->handler = $webSocketHandler;
    }


    public function __invoke(WebSocketConnection $connection): void
    {
        $constructedConnection = new Connection($connection);

        $this->handler->onOpen($constructedConnection);

        $connection->on('message', function (Message $message) use ($constructedConnection) {
            try {
                $constructedPayload = new Payload($constructedConnection, $message->getPayload());
                $this->handler->onMessage($constructedConnection, $constructedPayload);
            } catch (InvalidPayloadException | JsonException $payloadException) {
                $constructedConnection->send([
                    'command' => 'system.response.500',
                    'message' => $payloadException->getMessage()
                ]);
            }
        });

        $connection->on('error', function (Throwable $e) use ($constructedConnection) {
            $this->handler->onError($constructedConnection, $e);
        });

        $connection->on('close', function () use ($constructedConnection) {
            $this->handler->onClose($constructedConnection);
        });
    }
}