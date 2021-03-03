<?php


namespace Reactify\Websocket;


use Nette\Utils\JsonException;

/**
 * Class Notification
 * @package Reactify\Websocket
 */
class Notification
{
    private static string $title;
    private static string $body;
    private static string $icon = '';
    private static ConnectionInterface $connection;


    public static function create(ConnectionInterface $connection): Notification
    {
        return new Notification($connection);
    }

    public function __construct(ConnectionInterface $connection)
    {
        self::$connection = $connection;
    }

    public function title(string $title): Notification
    {
        self::$title = $title;
        return $this;
    }

    public function body(string $body): Notification
    {
        self::$body = $body;
        return $this;
    }

    public function icon(string $iconUrl): Notification
    {
        self::$icon = $iconUrl;
        return $this;
    }

    /**
     * @param array $notifData
     * @throws JsonException
     */
    public function send(array $notifData = []): void
    {
        if ([] === $notifData) {
            if (!isset(self::$title)) {
                throw new \Exception("Notification title must be provided");
            }
            if (!isset(self::$body)) {
                throw new \Exception("Notification body must be provided");
            }
            $notifData = [
                'title' => self::$title,
                'body' => self::$body,
                'icon' => self::$icon,
            ];
        }

        self::$connection->send('reactify.notification', $notifData);
    }
}