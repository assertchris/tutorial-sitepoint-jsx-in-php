<?php

require __DIR__ . "/vendor/autoload.php";

function pre_div($props) {
    $code = "<div";

    if (isset($props["className"])) {
        if (is_callable($props["className"])) {
            $class = $props["className"]();
        }

        else {
            $class = $props["className"];
        }

        $code .= " class='{$class}'";
    }

    $code .= ">";

    foreach ($props["children"] as $child) {
        $code .= $child;
    }

    $code .= "</div>";

    return trim($code);
}

function pre_span($props) {
    $code = pre_div($props);
    $code = preg_replace("#^<div#", "<span", $code);
    $code = preg_replace("#div>$#", "span>", $code);

    return $code;
}

function parse($nodes) {
    $code = "";

    foreach ($nodes as $node) {
        if (isset($node["token"])) {
            $code .= $node["token"] . PHP_EOL;
        }

        if (isset($node["tag"])) {
            $props = [];
            $attributes = [];

            if (isset($node["attributes"])) {
                foreach ($node["attributes"] as $key => $value) {
                    if (isset($value["token"])) {
                        $attributes["attr_{$key}"] = $value["token"];
                    }

                    if (isset($value["tag"])) {
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

            $children = [];

            foreach ($node["children"] as $child) {
                if (isset($child["tag"])) {
                    $children[] = parse([$child]);
                }

                else {
                    $children[] = "\"" . addslashes($child["token"]) . "\"";
                }
            }

            $props["children"] = $children;

            if (function_exists("pre_" . $node["name"])) {
                $code .= "pre_" . $node["name"] . "([" . PHP_EOL;
            }

            else {
                $code .= $node["name"] . "([" . PHP_EOL;
            }

            foreach ($props as $key => $value) {
                if ($key === "children") {
                    $code .= "\"children\" => [" . PHP_EOL;

                    foreach ($children as $child) {
                        $code .= "{$child}," . PHP_EOL;
                    }

                    $code .= "]," . PHP_EOL;
                }

                else {
                    $code .= "\"{$key}\" => {$value}," . PHP_EOL;
                }
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
                'token' => '() => { return $classNames; }',
            ],
            1 => [
                'tag' => '<span className={0}>',
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
    3 => [
        'token' => 'print_r($thing);',
    ],
];

eval(substr(parse($nodes), 5));

// <div class='foo bar'>
//     a bit of text before
//     <span>
//         hello world with a bit of extra text
//     </span>
//     a bit of text after
// </div>
