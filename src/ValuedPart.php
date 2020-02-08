<?php

declare(strict_types=1);

/*
 * This file is part of the PathToRegExpPHP library.
 * (c) Matías Navarro-Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MNC\PathToRegExpPHP;

/**
 * Class ValuedPart.
 *
 * Represents a part that has been assigned a value
 */
class ValuedPart
{
    /**
     * @var Part
     */
    private $part;
    /**
     * @var string|null
     */
    private $value;

    /**
     * ValuedPart constructor.
     *
     * @param Part   $part
     * @param string $value
     */
    public function __construct(Part $part, ?string $value)
    {
        $this->part = $part;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->part->getName();
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->part->getDelimiter();
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->part->isOptional();
    }

    /**
     * @return bool
     */
    public function isRepeat(): bool
    {
        return $this->part->isRepeat();
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }
}
