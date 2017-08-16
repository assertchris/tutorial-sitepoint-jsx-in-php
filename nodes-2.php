<?php

function nodes($tokens) {
    $nodes = [];
    $current = null;

    $cursor = 0;
    $length = count($tokens);

    while ($cursor < $length) {
        $token =& $tokens[$cursor];

        if (is_array($token) && $token["tag"][1] !== "/") {
            preg_match("#^<([a-zA-Z]+)#", $token["tag"], $matches);

            if ($current !== null) {
                $token["parent"] =& $current;
                $current["children"][] =& $token;
            } else {
                $token["parent"] = null;
                $nodes[] =& $token;
            }

            $current =& $token;
            $current["name"] = $matches[1];
            $current["children"] = [];

            if (isset($current["attributes"])) {
                foreach ($current["attributes"] as $key => $value) {
                    $current["attributes"][$key] = nodes($value);
                }

                $current["attributes"] = array_map(function($item) {

                    foreach ($item as $value) {
                        if (isset($value["tag"])) {
                            return $value;
                        }
                    }

                    foreach ($item as $value) {
                        if (!empty($value["token"])) {
                            return $value;
                        }
                    }

                    return null;

                }, $current["attributes"]);
            }
        }

        else if (is_array($token) && $token["tag"][1] === "/") {
            preg_match("#^</([a-zA-Z]+)#", $token["tag"], $matches);

            if ($current === null) {
                throw new Exception("no open tag");
            }

            if ($matches[1] !== $current["name"]) {
                throw new Exception("no matching open tag");
            }

            if ($current !== null) {
                $current =& $current["parent"];
            }
        }

        else if ($current !== null) {
            array_push($current["children"], [
                "parent" => &$current,
                "token" => &$token,
            ]);
        }

        else {
            array_push($nodes, [
                "token" => $token,
            ]);
        }

        $cursor++;
    }

    return $nodes;
}

$tokens = [
    0 => '<?php

    $classNames = "foo bar";
    $message = "hello world";

    $thing = (',
    1 => [
        'tag' => '<div className={0} nested={1}>',
        'started' => 157,
        'attributes' => [
            0 => [
                0 => '() => { return "outer-div"; }',
            ],
            1 => [
                1 => [
                    'tag' => '<span className={0}>',
                    'started' => 19,
                    'attributes' => [
                        0 => [
                            0 => '"nested-span"',
                        ],
                    ],
                ],
                2 => 'with text',
                3 => [
                    'tag' => '</span>',
                    'started' => 34,
                ]
            ],
        ],
    ],
    2 => 'a bit of text before',
    3 => [
        'tag' => '<span>',
        'started' => 195,
    ],
    4 => '{$message} with a bit of extra text',
    5 => [
        'tag' => '</span>',
        'started' => 249,
    ],
    6 => 'a bit of text after',
    7 => [
        'tag' => '</div>',
        'started' => 282,
    ],
    8 => ');',
];

nodes($tokens);

// Array
// (
//     [0] => Array
//         (
//             [token] => <?php
//
//     $classNames = "foo bar";
//     $message = "hello world";
//
//     $thing = (
//         )
//
//     [1] => Array
//         (
//             [tag] => <div className={0} nested={1}>
//             [started] => 157
//             [attributes] => Array
//                 (
//                     [0] => Array
//                         (
//                             [token] => () => { return "outer-div"; }
//                         )
//
//                     [1] => Array
//                         (
//                             [tag] => <span className={0}>
//                             [started] => 19
//                             [attributes] => Array
//                                 (
//                                     [0] => Array
//                                         (
//                                             [token] => "nested-span"
//                                         )
//
//                                 )
//
//                             [parent] =>
//                             [name] => span
//                             [children] => Array
//                                 (
//                                     [0] => Array
//                                         (
//                                             [parent] => *RECURSION*
//                                             [token] => with text
//                                         )
//
//                                 )
//
//                         )
//
//                 )
//
//             [parent] =>
//             [name] => div
//             [children] => Array
//                 (
//                     [0] => Array
//                         (
//                             [parent] => *RECURSION*
//                             [token] => a bit of text before
//                         )
//
//                     [1] => Array
//                         (
//                             [tag] => <span>
//                             [started] => 195
//                             [parent] => *RECURSION*
//                             [name] => span
//                             [children] => Array
//                                 (
//                                     [0] => Array
//                                         (
//                                             [parent] => *RECURSION*
//                                             [token] => {$message} with ...
//                                         )
//
//                                 )
//
//                         )
//
//                     [2] => Array
//                         (
//                             [parent] => *RECURSION*
//                             [token] => a bit of text after
//                         )
//
//                 )
//
//         )
//
//     [2] => Array
//         (
//             [token] => );
//         )
//
// )
