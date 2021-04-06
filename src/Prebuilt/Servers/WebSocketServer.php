<?php


namespace Reactificate\Websocket\Prebuilt\Servers;


use Reactificate\Utils\Console;
use Reactificate\Utils\Loop;
use Reactificate\Websocket\ConnectionInterface;
use Reactificate\Websocket\Notification;
use Reactificate\Websocket\Payload;
use Reactificate\Websocket\ServerInfo;
use Reactificate\Websocket\WebSocketHandlerInterface;
use Throwable;

class WebSocketServer implements WebSocketHandlerInterface
{

    public function onMessage(ConnectionInterface $connection, Payload $payload): void
    {
        if ('notification' === $payload->command()) {
            Notification::create($connection)
                ->title(uniqid())
                ->body($payload->message())
                ->send();
        } else {
            $connection->send([
                'time' => date('H:i:s'),
                'message' => strtoupper($payload->message())
            ]);
        }

        Console::echo($payload->message() . PHP_EOL);
    }

    public function onOpen(ConnectionInterface $connection): void
    {
        Loop::interval(1, function () {
            //$connection->send(microtime(true));
        });

        Console::echo("New connection: {$connection->getId()}\n");
    }

    public function onClose(ConnectionInterface $connection): void
    {
        // TODO: Implement onClose() method.
    }

    public function onError(ConnectionInterface $connection, Throwable $exception): void
    {
        // TODO: Implement onError() method.
    }

    public function getServerInfo(): ServerInfo
    {
        $info = new ServerInfo();
        $info->setPrefix('/ws/test');

        return $info;
    }
}