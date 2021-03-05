<?php


namespace Reactificate\Websocket;


use JsonSerializable;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Reactificate\Websocket\Exceptions\InvalidPayloadException;
use stdClass;

class Payload implements JsonSerializable
{
    protected string $originalPayload;

    protected stdClass $decodedPayload;


    /**
     * Payload constructor.
     * @param string $strPayload
     * @throws InvalidPayloadException
     * @throws JsonException
     */
    public function __construct(string $strPayload)
    {
        $this->decodedPayload = Json::decode($strPayload);

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
     * Get sent time
     * @return int
     */
    public function time(): int
    {
        return $this->decodedPayload->time ?? 0;
    }

    /**
     * Get sent command
     * @return string|null
     */
    public function command(): ?string
    {
        return $this->decodedPayload->command ?? null;
    }

    /**
     * Get sent message
     * @return string|null
     */
    public function message(): ?string
    {
        return $this->decodedPayload->message ?? null;
    }

    /**
     * Get sent authentication token
     * @return string|null
     */
    public function token(): ?string
    {
        return $this->decodedPayload->token ?? null;
    }

    /**
     * Gets original payload
     * @return string
     */
    public function originalPayload(): string
    {
        return $this->originalPayload;
    }

}