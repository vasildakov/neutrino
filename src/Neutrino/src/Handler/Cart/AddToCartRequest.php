<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neutrino\Handler\Cart;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

use function in_array;
use function is_array;

/**
 * Add to Cart Request DTO
 */
final readonly class AddToCartRequest
{
    public function __construct(
        public string $itemType,
        public string $itemId,
        public string $billingPeriod,
        public int $quantity
    ) {
    }

    /**
     * Create from array data
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            itemType: $data['item_type'] ?? 'plan',
            itemId: $data['item_id'] ?? throw new InvalidArgumentException('Item ID is required'),
            billingPeriod: $data['billing_period'] ?? 'monthly',
            quantity: (int) ($data['quantity'] ?? 1)
        );
    }

    /**
     * Create from a request parsed body
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $data = $request->getParsedBody();

        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid request body');
        }

        return self::fromArray($data);
    }

    /**
     * Check if this is a plan item
     */
    public function isPlan(): bool
    {
        return $this->itemType === 'plan';
    }

    /**
     * Check if this is an addon item
     */
    public function isAddon(): bool
    {
        return $this->itemType === 'addon';
    }

    /**
     * Validate the request
     */
    public function validate(): void
    {
        if (empty($this->itemId)) {
            throw new InvalidArgumentException('Item ID is required');
        }

        if (! in_array($this->itemType, ['plan', 'addon', 'extra'], true)) {
            throw new InvalidArgumentException('Invalid item type');
        }

        if (! in_array($this->billingPeriod, ['monthly', 'yearly'], true)) {
            throw new InvalidArgumentException('Invalid billing period');
        }

        if ($this->quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1');
        }
    }
}
