<?php

declare(strict_types=1);

namespace App\Domain\Communication\Channels;

use App\Domain\Communication\Contracts\CommunicationChannelInterface;
use InvalidArgumentException;

class ChannelManager
{
    /**
     * @var array<string, CommunicationChannelInterface>
     */
    protected array $drivers = [];

    public function __construct()
    {
        // Register default channels
        $this->registerDriver(new EmailChannelDriver());
        $this->registerDriver(new WhatsAppChannelDriver());
    }

    public function registerDriver(CommunicationChannelInterface $driver): void
    {
        $this->drivers[strtolower($driver->getName())] = $driver;
    }

    public function getDriver(string $channel): CommunicationChannelInterface
    {
        $ch = strtolower($channel);
        if (!isset($this->drivers[$ch])) {
            throw new InvalidArgumentException("Communication Channel Driver '{$channel}' is not registered.");
        }

        return $this->drivers[$ch];
    }

    public function hasDriver(string $channel): bool
    {
        return isset($this->drivers[strtolower($channel)]);
    }

    public function getRegisteredChannels(): array
    {
        return array_keys($this->drivers);
    }
}
