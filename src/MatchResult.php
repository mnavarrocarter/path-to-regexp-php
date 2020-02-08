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
 * Class MatchResult.
 */
class MatchResult
{
    /**
     * @var string
     */
    private $matchedString;
    /**
     * @var ValuedPart[]
     */
    private $parts;

    /**
     * MatchResult constructor.
     *
     * @param string     $matchedString
     * @param ValuedPart ...$parts
     */
    public function __construct(string $matchedString, ValuedPart ...$parts)
    {
        $this->matchedString = $matchedString;
        $this->parts = $parts;
    }

    public function getMatchedString(): string
    {
        return $this->matchedString;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        $values = [];
        foreach ($this->parts as $part) {
            $values[$part->getName()] = $part->getValue();
        }

        return $values;
    }
}
