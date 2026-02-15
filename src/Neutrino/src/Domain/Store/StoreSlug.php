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
namespace Neutrino\Domain\Store;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use function sprintf;
use function strlen;

#[ORM\Embeddable]
final class StoreSlug
{
    private const SLUG_LENGTH = 16;

    #[ORM\Column(name: 'slug', type: 'string', length: 16)]
    private readonly string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromGenerator(SlugGeneratorInterface $generator): self
    {
        $slug = $generator->generate();

        if (strlen($slug) !== self::SLUG_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Slug must be exactly %d characters long, got %d', self::SLUG_LENGTH, strlen($slug))
            );
        }

        return new self($slug);
    }

    public static function fromString(string $value): self
    {
        if (strlen($value) !== self::SLUG_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Slug must be exactly %d characters long, got %d', self::SLUG_LENGTH, strlen($value))
            );
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
