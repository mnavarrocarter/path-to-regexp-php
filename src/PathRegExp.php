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
 * Class PathRegExp.
 *
 * Represents a regular expression created from a path
 */
class PathRegExp
{
    /**
     * The pattern to match.
     *
     * @var string
     */
    private $pattern;
    /**
     * @var Part[]
     */
    private $parts;

    /**
     * PathRegExp constructor.
     *
     * @param string $pattern
     * @param Part[] $parts
     */
    public function __construct(string $pattern, Part ...$parts)
    {
        $this->pattern = $pattern;
        $this->parts = $parts;
    }

    /**
     * Matches a route path against this path regular expression.
     *
     * @param string $route the route path
     * @param int    $flags
     *
     * @return MatchResult
     *
     * @throws NoMatchException if there is no match
     */
    public function match(string $route, int $flags = 0): MatchResult
    {
        $result = preg_match_all($this->pattern, $route, $matches, $flags);

        if (count($matches) === 0) {
            throw new NoMatchException(sprintf('Not match found for %s', $route));
        }
        if ($result === false) {
            throw new MatchErrorException('Match error');
        }
        $matched = array_shift($matches);
        if (!isset($matched[0]) || !is_string($matched[0])) {
            throw new NoMatchException('No path has been matched');
        }
        $valuedParts = [];
        foreach ($matches as $key => $match) {
            if (count($match) === 0) {
                throw new NoMatchException(sprintf('Invalid matched part %s', $this->parts[$key]->getName()));
            }
            if ($match[0] === '') {
                $match[0] = null;
            }
            $valuedParts[] = new ValuedPart($this->parts[$key], $match[0]);
        }

        return new MatchResult($matched[0], ...$valuedParts);
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return Part[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }
}
