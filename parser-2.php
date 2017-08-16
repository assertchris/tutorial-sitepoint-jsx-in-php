<?php

require __DIR__ . "/vendor/autoload.php";

function parse($nodes) {
    $code = "";

    foreach ($nodes as $node) {
        if (isset($node["token"])) {
            $code .= $node["token"] . PHP_EOL;
        }

        if (isset($node["tag"])) {
            $props = [];
            $attributes = [];
            $elements = [];

            if (isset($node["attributes"])) {
                foreach ($node["attributes"] as $key => $value) {
                    if (isset($value["token"])) {
                        $attributes["attr_{$key}"] = $value["token"];
                    }

                    if (isset($value["tag"])) {
                        $elements[$key] = true;
                        $attributes["attr_{$key}"] = parse([$value]);
                    }
                }
            }

            preg_match_all("#([a-zA-Z]+)={([^}]+)}#", $node["tag"], $dynamic);
            preg_match_all("#([a-zA-Z]+)=[']([^']+)[']#", $node["tag"], $static);

            if (count($dynamic[0])) {
                foreach($dynamic[1] as $key => $value) {
                    $props["{$value}"] = $attributes["attr_{$key}"];
                }
            }

            if (count($static[1])) {
                foreach($static[1] as $key => $value) {
                    $props["{$value}"] = $static[2][$key];
                }
            }

            $code .= "pre_" . $node["name"] . "([" . PHP_EOL;

            foreach ($props as $key => $value) {
                $code .= "'{$key}' => {$value}," . PHP_EOL;
            }

            $code .= "])" . PHP_EOL;
        }
    }

    $code = Pre\Plugin\expand($code);
    $code = Pre\Plugin\formatCode($code);

    return $code;
}

$nodes = [
    0 => [
        'token' => '<?php

        $classNames = "foo bar";
        $message = "hello world";

        $thing = (',
    ],
    1 => [
        'tag' => '<div className={0} nested={1}>',
        'started' => 157,
        'attributes' => [
            0 => [
                'token' => '() => { return "outer-div"; }',
            ],
            1 => [
                'tag' => '<span className={0} foo="bar">',
                'started' => 19,
                'attributes' => [
                    0 => [
                        'token' => '"nested-span"',
                    ],
                ],
                'name' => 'span',
                'children' => [
                    0 => [
                        'token' => 'with text',
                    ],
                ],
            ],
        ],
        'name' => 'div',
        'children' => [
            0 => [
                'token' => 'a bit of text before',
            ],
            1 => [
                'tag' => '<span>',
                'started' => 195,
                'name' => 'span',
                'children' => [
                    0 => [
                        'token' => '{$message} with a bit of extra text',
                    ],
                ],
            ],
            2 => [
                'token' => 'a bit of text after',
            ],
        ],
    ],
    2 => [
        'token' => ');',
    ],
];

parse($nodes);

// <?php
//
// $classNames = "foo bar";
// $message = "hello world";
//
// $thing = (
//     pre_div([
//         'className' => function () {
//             return "outer-div";
//         },
//         'nested' => pre_span([
//             'className' => "nested-span",
//         ]),
//     ])
// );
