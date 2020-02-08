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
 * Class PathRegExpFactory.
 */
class PathRegExpFactory
{
    public const STRICT = 1;
    public const END = 2;
    public const CASE_SENSITIVE = 4;

    /**
     * Creates a Path Regular Expression from a string.
     *
     * @param string $path
     * @param int    $flags
     *
     * @return PathRegExp
     */
    public static function create(string $path, int $flags = 2): PathRegExp
    {
        $parts = [];
        $strict = ($flags & self::STRICT) !== 0 ?: false;
        $end = ($flags & self::END) !== 0 ?: false;
        $regexpFlags = ($flags & self::CASE_SENSITIVE) !== 0 ? '' : 'i';

        $index = 0;

        $pathRegexps = [
            // Match already escaped characters that would otherwise incorrectly appear
            // in future matches. This allows the user to escape special characters that
            // shouldn't be transformed.
            '(\\\\.)',
            // Match Express-style parameters and un-named parameters with a prefix
            // and optional suffixes. Matches appear as:
            //
            // "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?"]
            // "/route(\\d+)" => [undefined, undefined, undefined, "\d+", undefined]
            '([\\/.])?(?:\\:(\\w+)(?:\\(((?:\\\\.|[^)])*)\\))?|\\(((?:\\\\.|[^)])*)\\))([+*?])?',
            // Match regexp special characters that should always be escaped.
            '([.+*?=^!:${}()[\\]|\\/])',
        ];
        $pathRegexp = '/'.implode('|', $pathRegexps).'/';

        // Alter the path string into a usable regexp.
        $path = preg_replace_callback($pathRegexp, static function (array $matches) use (&$parts, &$index) {
            if (count($matches) > 1) {
                $escaped = $matches[1];
            }
            if (count($matches) > 2) {
                $prefix = $matches[2];
            }
            if (count($matches) > 3) {
                $key = $matches[3];
            }
            if (count($matches) > 4) {
                $capture = $matches[4];
            }
            if (count($matches) > 5) {
                $group = $matches[5];
            }
            if (count($matches) > 6) {
                $suffix = $matches[6];
            } else {
                $suffix = '';
            }
            if (count($matches) > 7) {
                $escape = $matches[7];
            }

            // Avoiding re-escaping escaped characters.
            if (!empty($escaped)) {
                return $escaped;
            }

            // Escape regexp special characters.
            if (!empty($escape)) {
                return '\\'.$escape;
            }

            $repeat = $suffix === '+' || $suffix === '*';
            $optional = $suffix === '?' || $suffix === '*';

            $parts[] = new Part(
                (string) (!empty($key) ? $key : $index++),
                !empty($prefix) ? $prefix : '/',
                $optional,
                $repeat
            );

            // Escape the prefix character.
            $prefix = !empty($prefix) ? '\\'.$prefix : '';

            // Match using the custom capturing group, or fallback to capturing
            // everything up to the next slash (or next period if the param was
            // prefixed with a period).
            $subject = (!empty($capture) ? $capture : (!empty($group) ? $group : '[^'.(!empty($prefix) ? $prefix : '\\/').']+?'));
            $capture = preg_replace('/([=!:$\/()])/', '\1', $subject);

            // Allow parameters to be repeated more than once.
            if ($repeat === true) {
                $capture = $capture.'(?:'.$prefix.$capture.')*';
            }

            // Allow a parameter to be optional.
            if ($optional === true) {
                return '(?:'.$prefix.'('.$capture.'))?';
            }

            // Basic parameter support.
            return $prefix.'('.$capture.')';
        }, $path);

        // Check whether the path ends in a slash as it alters some match behaviour.
        $endsWithSlash = substr($path, -1, 1) === '/';

        // In non-strict mode we allow an optional trailing slash in the match. If
        // the path to match already ended with a slash, we need to remove it for
        // consistency. The slash is only valid at the very end of a path match, not
        // anywhere in the middle. This is important for non-ending mode, otherwise
        // "/test/" will match "/test//route".
        if ($strict === false) {
            $path = ($endsWithSlash ? substr($path, 0, -2) : $path).'(?:\\/(?=$))?';
        }

        // In non-ending mode, we need prompt the capturing groups to match as much
        // as possible by using a positive lookahead for the end or next path segment.
        if ($end === false) {
            $path .= $strict && $endsWithSlash ? '' : '(?=\\/|$)';
        }

        $pattern = '/^'.$path.($end ? '$' : '').'/'.$regexpFlags;

        return new PathRegExp($pattern, ...$parts);
    }
}
