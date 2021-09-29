<?php


namespace Reactificate\Websocket;


use Exception;
use InvalidArgumentException;
use Nette\Utils\JsonException;

/**
 * Class Notification
 * @package Reactificate\Websocket
 */
class Notification
{
    private array $notificationData = [
        'title' => null,
        'body' => null,
        'icon' => null,
        'vibrate' => false,
        'silent' => false,
        'redirect' => null,
    ];

    private static ConnectionInterface $connection;


    /**
     * Creates new notification instance
     *
     * @param ConnectionInterface $connection
     * @return Notification
     */
    public static function create(ConnectionInterface $connection): Notification
    {
        return new Notification($connection);
    }

    /**
     * Publish notification using an array of title, body...
     *
     * @param ConnectionInterface $connection
     * @param array $notificationData
     * @throws JsonException
     */
    public static function publish(ConnectionInterface $connection, array $notificationData): void
    {
        Notification::create($connection)->send($notificationData);
    }

    public function __construct(ConnectionInterface $connection)
    {
        self::$connection = $connection;
    }

    public function setField(string $key, $value): Notification
    {
        $this->notificationData[$key] = $value;
        return $this;
    }

    public function title(string $title): Notification
    {
        return $this->setField('title', $this);
    }

    public function body(string $body): Notification
    {
        return $this->setField('body', $body);
    }

    public function icon(string $iconUrl): Notification
    {
        return $this->setField('icon', $iconUrl);
    }

    public function vibrate(bool $vibrate = false): Notification
    {
        return $this->setField('vibrate', $vibrate);
    }

    public function silent(bool $silent = false): Notification
    {
        return $this->setField('silent', $silent);
    }

    /**
     * Sets a link to be redirected when the notification is clicked
     *
     * @param string $url
     * @return $this
     */
    public function redirect(string $url): Notification
    {
        return $this->setField('redirect', $url);
    }


    /**
     * Sends the notification to clientside
     *
     * @param array $notificationData
     * @throws JsonException
     * @throws Exception
     */
    public function send(array $notificationData = []): void
    {
        $notificationData = array_merge($this->notificationData, $notificationData);

        if (!empty($notificationData['title'])) {
            throw new InvalidArgumentException('Notification title must be provided and not empty');
        }

        if (!empty($notificationData['body'])) {
            throw new InvalidArgumentException('Notification body must be provided and not empty');
        }

        self::$connection->send('Reactificate.Notification', $notificationData);
    }
}