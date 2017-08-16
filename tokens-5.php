<?php

function tokens($code) {
    $tokens = [];

    $length = strlen($code);
    $cursor = 0;

    $elementLevel = 0;
    $elementStarted = null;
    $elementEnded = null;

    $attributes = [];
    $attributeLevel = 0;
    $attributeStarted = null;
    $attributeEnded = null;

    $carry = 0;

    while ($cursor < $length) {
        if ($code[$cursor] === "{" && $elementStarted !== null) {
            if ($attributeLevel === 0) {
                $attributeStarted = $cursor;
            }

            $attributeLevel++;
        }

        if ($code[$cursor] === "}" && $elementStarted !== null) {
            $attributeLevel--;

            if ($attributeLevel === 0) {
                $attributeEnded = $cursor;
            }
        }

        if ($attributeStarted && $attributeEnded) {
            $position = (string) count($attributes);
            $positionLength = strlen($position);

            $attribute = substr(
                $code, $attributeStarted + 1, $attributeEnded - $attributeStarted - 1
            );

            $attributes[$position] = $attribute;

            $before = substr($code, 0, $attributeStarted + 1);
            $after = substr($code, $attributeEnded);

            $code = $before . $position . $after;

            $cursor = $attributeStarted + $positionLength + 2 /* curlies */;
            $length = strlen($code);

            $attributeStarted = null;
            $attributeEnded = null;

            continue;
        }

        preg_match("#^</?[a-zA-Z]#", substr($code, $cursor, 3), $matchesStart);

        if (count($matchesStart) && $attributeLevel < 1) {
            $elementLevel++;
            $elementStarted = $cursor;
        }

        preg_match("#^=>#", substr($code, $cursor - 1, 2), $matchesEqualBefore);
        preg_match("#^>=#", substr($code, $cursor, 2), $matchesEqualAfter);

        if (
            $code[$cursor] === ">"
            && !$matchesEqualBefore && !$matchesEqualAfter
            && $attributeLevel < 1
        ) {
            $elementLevel--;
            $elementEnded = $cursor;
        }

        if ($elementStarted !== null && $elementEnded !== null) {
            $distance = $elementEnded - $elementStarted;

            $carry += $cursor;

            $before = trim(substr($code, 0, $elementStarted));
            $tag = trim(substr($code, $elementStarted, $distance + 1));
            $after = trim(substr($code, $elementEnded + 1));

            $token = ["tag" => $tag, "started" => $carry];

            foreach ($attributes as $key => $value) {
                $attributes[$key] = tokens($value);
            }

            if (count($attributes)) {
                $token["attributes"] = $attributes;
            }

            $tokens[] = $before;
            $tokens[] = $token;

            $attributes = [];

            $code = $after;
            $length = strlen($code);
            $cursor = 0;

            $elementStarted = null;
            $elementEnded = null;

            continue;
        }

        $cursor++;
    }

    $tokens[] = trim($code);

    return array_filter($tokens);
}

$code = '
    <?php

    $classNames = "foo bar";
    $message = "hello world";

    $thing = (
        <div
            className={() => { return "outer-div"; }}
            nested={<span className={"nested-span"}>with text</span>}
        >
            a bit of text before
            <span>
                {$message} with a bit of extra text
            </span>
            a bit of text after
        </div>
    );
';

tokens($code);

// Array
// (
//     [0] => <?php
//
//     $classNames = "foo bar";
//     $message = "hello world";
//
//     $thing = (
//     [1] => Array
//         (
//             [tag] => <div
//             className={0}
//             nested={1}
//         >
//             [started] => 157
//             [attributes] => Array
//                 (
//                     [0] => Array
//                         (
//                             [0] => () => { return "outer-div"; }
//                         )
//
//                     [1] => Array
//                         (
//                             [1] => Array
//                                 (
//                                     [tag] => <span className={0}>
//                                     [started] => 19
//                                     [attributes] => Array
//                                         (
//                                             [0] => Array
//                                                 (
//                                                     [0] => "nested-span"
//                                                 )
//
//                                         )
//
//                                 )
//
//                            [2] => with text
//                            [3] => Array
//                                (
//                                    [tag] => </span>
//                                    [started] => 34
//                                )
//                         )
//
//                 )
//
//         )
//
//     [2] => a bit of text before
//     [3] => Array
//         (
//             [tag] => <span>
//             [started] => 195
//         )
//
//     [4] => {$message} with a bit of extra text
//     [5] => Array
//         (
//             [tag] => </span>
//             [started] => 249
//         )
//
//     [6] => a bit of text after
//     [7] => Array
//         (
//             [tag] => </div>
//             [started] => 282
//         )
//
//     [8] => );
// )
