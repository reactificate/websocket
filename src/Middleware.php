<?php


namespace Reactificate\Websocket;


use Nette\Utils\JsonException;
use Ratchet\RFC6455\Messaging\Message;
use Reactificate\Websocket\Exceptions\InvalidPayloadException;
use Throwable;
use Voryx\WebSocketMiddleware\WebSocketConnection;

class Middleware
{
    protected WebSocketHandlerInterface $handler;


    public function __construct(WebSocketHandlerInterface $socketHandler)
    {
        $this->handler = $socketHandler;
    }


    public function __invoke(WebSocketConnection $connection): void
    {
        $constructedConnection = new Connection($connection);

        $this->handler->onOpen($constructedConnection);

        $connection->on('message', function (Message $message) use ($constructedConnection) {
            try {
                $constructedPayload = new Payload($message->getPayload());
                $this->handler->onMessage($constructedConnection, $constructedPayload);
            } catch (InvalidPayloadException|JsonException $payloadException) {
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