<?php

namespace Neutrino\Authentication\Handler\Google;

final readonly class GoogleUserResolveResult
{
    private function __construct(
        private bool $success,
        private ?string $userId,
    ) {}

    public static function success(string $userId): self
    {
        return new self(true, $userId);
    }

    public static function fail(): self
    {
        return new self(false, null);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function userId(): string
    {
        return (string) $this->userId;
    }
}
