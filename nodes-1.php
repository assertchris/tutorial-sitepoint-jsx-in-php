<?php

function nodes($tokens) {
    $cursor = 0;
    $length = count($tokens);

    while ($cursor < $length) {
        $token = $tokens[$cursor];

        if (is_array($token) && $token["tag"][1] !== "/") {
            preg_match("#^<([a-zA-Z]+)#", $token["tag"], $matches);

            print "OPENING {$matches[1]}" . PHP_EOL;
        }

        if (is_array($token) && $token["tag"][1] === "/") {
            preg_match("#^</([a-zA-Z]+)#", $token["tag"], $matches);

            print "CLOSING {$matches[1]}" . PHP_EOL;
        }

        $cursor++;
    }

    return $tokens;
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

// OPENING div
// OPENING span
// CLOSING span
// CLOSING div
