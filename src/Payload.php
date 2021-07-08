<?php


namespace Reactificate\Websocket;


use JsonSerializable;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Reactificate\Websocket\Exceptions\InvalidPayloadException;

class Payload implements JsonSerializable
{
    protected string $originalPayload;

    protected array $decodedPayload;

    protected ConnectionInterface $connection;


    /**
     * Payload constructor.
     * @param ConnectionInterface $connection
     * @param string $strPayload
     * @throws InvalidPayloadException
     * @throws JsonException
     */
    public function __construct(ConnectionInterface $connection, string $strPayload)
    {
        $this->decodedPayload = Json::decode($strPayload, Json::FORCE_ARRAY);
        $this->connection = $connection;
        $this->originalPayload = $strPayload;

        if (!$this->decodedPayload->command) {
            InvalidPayloadException::create('No payload command specified.');
        }
    }

    public function __toString(): string
    {
        return $this->message();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return (array)$this->decodedPayload;
    }

    /**
     * Gets payload's client connection
     *
     * @return ConnectionInterface
     */
    public function connection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Get sent time
     *
     * @return int
     */
    public function time(): int
    {
        return $this->decodedPayload['time'] ?? 0;
    }

    /**
     * Get sent command
     *
     * @return string|null
     */
    public function command(): ?string
    {
        return $this->decodedPayload['command'] ?? null;
    }

    /**
     * Get sent message
     *
     * @return string|array|null
     */
    public function message(?string $key = null)
    {
        $message = $this->decodedPayload['message'] ?? null;

        if ($key) {
            if (
                $message
                && is_object($message)
                && isset($message[$key])
            ) {
                return $message[$key];
            }

            return null;
        }

        return $message;
    }

    /**
     * Get sent authentication token
     *
     * @return string|null
     */
    public function token(): ?string
    {
        return $this->decodedPayload['token'] ?? null;
    }

    /**
     * Gets original payload
     *
     * @return string
     */
    public function originalPayload(): string
    {
        return $this->originalPayload;
    }

}