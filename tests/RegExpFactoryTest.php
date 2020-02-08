<?php

declare(strict_types=1);

/*
 * This file is part of the PathToRegExpPHP library.
 * (c) Matías Navarro-Carter <mnavarrocarter@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MNC\PathToRegExpPHP\Tests;

use MNC\PathToRegExpPHP\NoMatchException;
use MNC\PathToRegExpPHP\PathRegExpFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RegExpFactoryTest.
 */
class RegExpFactoryTest extends TestCase
{
    public function testCreation(): void
    {
        $path = PathRegExpFactory::create('/user/:name');
        $this->assertCount(1, $path->getParts());
        $this->assertSame('name', $path->getParts()[0]->getName());
        $this->assertSame('/', $path->getParts()[0]->getDelimiter());
        $this->assertFalse($path->getParts()[0]->isRepeat());
        $this->assertFalse($path->getParts()[0]->isOptional());
        $this->assertSame('/^\/user\/([^\/]+?)(?:\/(?=$))?$/i', $path->getPattern());
    }

    public function testSimpleMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:name');
        $result = $path->match('/user/john');
        $this->assertSame('/user/john', $result->getMatchedString());
        $this->assertSame(['name' => 'john'], $result->getValues());

        $this->assertSame('name', $result->getParts()[0]->getName());
        $this->assertSame('/', $result->getParts()[0]->getDelimiter());
        $this->assertFalse($result->getParts()[0]->isOptional());
        $this->assertFalse($result->getParts()[0]->isRepeat());
        $this->assertSame('john', $result->getParts()[0]->getValue());
    }

    public function testDoesNotMatch(): void
    {
        $path = PathRegExpFactory::create('/meme/:name/:lastname');
        $this->expectException(NoMatchException::class);
        $path->match('/user/john');
    }

    public function testOptionalSimpleMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:name?');
        $result = $path->match('/user');
        $this->assertSame('/user', $result->getMatchedString());
        $this->assertSame(['name' => null], $result->getValues());
    }

    public function testTwoElementsMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:name/:lastname');
        $result = $path->match('/user/john/doe');
        $this->assertSame('/user/john/doe', $result->getMatchedString());
        $this->assertSame(['name' => 'john', 'lastname' => 'doe'], $result->getValues());
    }

    public function testTwoElementOptionalMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:name/:lastname?');
        $result = $path->match('/user/john');
        $this->assertSame('/user/john', $result->getMatchedString());
        $this->assertSame(['name' => 'john', 'lastname' => null], $result->getValues());
    }

    public function testManyMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:name+');
        $result = $path->match('/user/john/doe');
        $this->assertSame('/user/john/doe', $result->getMatchedString());
        $this->assertSame(['name' => 'john/doe'], $result->getValues());
    }

    public function testNumberMatches(): void
    {
        $path = PathRegExpFactory::create('/user/:id(\\d+)');
        $result = $path->match('/user/353');
        $this->assertSame('/user/353', $result->getMatchedString());
        $this->assertSame(['id' => '353'], $result->getValues());
    }

    public function testNumberDoesNotMatch(): void
    {
        $path = PathRegExpFactory::create('/user/:id(\d)');
        $this->expectException(NoMatchException::class);
        $path->match('/user/id');
    }

    public function testUnnamedParameterMatch(): void
    {
        $pathRegex = PathRegExpFactory::create('/:foo/(.*)');

        $result = $pathRegex->match('/test/route');
        $this->assertSame('/test/route', $result->getMatchedString());
        $this->assertSame(['foo' => 'test', '0' => 'route'], $result->getValues());
    }
}
