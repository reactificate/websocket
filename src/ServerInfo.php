<?php


namespace Reactificate\Websocket;


class ServerInfo
{
    protected string $serverPrefix;


    /**
     * Set websocket server prefix
     * @param string $serverPrefix
     * @return $this
     */
    public function setPrefix(string $serverPrefix): ServerInfo
    {
        $this->serverPrefix = $serverPrefix;
        return $this;
    }

    public function getInfo(): string
    {
        return $this->serverPrefix;
    }
}