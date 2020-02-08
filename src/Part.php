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
 * Class Part.
 *
 * Represents a placeholder in a path declared with variables, ie '/user/:id'
 */
class Part
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $delimiter;
    /**
     * @var bool
     */
    private $optional;
    /**
     * @var bool
     */
    private $repeat;

    /**
     * Part constructor.
     *
     * @param string $name
     * @param string $delimiter
     * @param bool   $optional
     * @param bool   $repeat
     */
    public function __construct(string $name, string $delimiter = '/', bool $optional = false, bool $repeat = false)
    {
        $this->name = $name;
        $this->delimiter = $delimiter;
        $this->optional = $optional;
        $this->repeat = $repeat;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isRepeat(): bool
    {
        return $this->repeat;
    }
}
