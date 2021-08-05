<?php

declare(strict_types=1);

namespace Divido\Nordea\AuthCode;

class AuthCode
{
    private $value;
    private $expiry;

    public function __construct(?string $value, int $expiry)
    {
        $this->value = $value;
        $this->expiry = $expiry;
    }

    public function getValue(): ?string
    {
        if ($this->isExpired()) {
            return null;
        }

        return $this->value;
    }

    /**
     * @return int
     */
    public function getExpiry(): int
    {
        return $this->expiry;
    }

    private function isExpired(): bool
    {
        return time() > $this->expiry;
    }
}
