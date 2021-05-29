<?php


namespace Reactificate\Websocket\Prebuilt\Servers;


use Reactificate\Websocket\ConnectionInterface;
use Reactificate\Websocket\Payload;
use Reactificate\Websocket\ServerInfo;
use Reactificate\Websocket\WebSocketHandlerInterface;
use Throwable;

class ChatServer implements WebSocketHandlerInterface
{

    /**
     * @var ConnectionInterface[]
     */
    protected array $users = [];

    /**
     * @inheritDoc
     */
    public function getServerInfo(): ServerInfo
    {
        return (new ServerInfo())->setPrefix('/ws/chat');
    }

    /**
     * @inheritDoc
     */
    public function onMessage(ConnectionInterface $connection, Payload $payload): void
    {
        if ('chat.message' == $payload->command()){
            foreach ($this->users as $user){
                $user->send('chat.message',  [
                    'username' => $payload->message()->username,
                    'message' => $payload->message()->message
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function onOpen(ConnectionInterface $connection): void
    {
        $this->users[$connection->getId()] = $connection;
    }

    /**
     * @inheritDoc
     */
    public function onClose(ConnectionInterface $connection): void
    {
        unset($this->users[$connection->getId()]);
    }

    /**
     * @inheritDoc
     */
    public function onError(ConnectionInterface $connection, Throwable $exception): void
    {
        // TODO: Implement onError() method.
    }
}