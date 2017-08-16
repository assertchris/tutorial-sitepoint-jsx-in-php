<?php

function parse($nodes, $format = true) {
    $code = "";

    foreach ($nodes as $node) {
        if (isset($node["token"])) {
            $code .= $node["token"] . PHP_EOL;
        }
    }

    if ($format) {
        $code = formatCode(preg_replace("/\n[ \t]+/", "\n", $code));
    }

    return $code;
}

function formatCode($code) {
    $dir = sys_get_temp_dir();
    $name = tempnam($dir, "pre");

    file_put_contents($name, $code);

    formatFile($name);
    $formatted = file_get_contents($name);

    unlink($name);

    return $formatted;
}

require __DIR__ . "/vendor/autoload.php";

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

function formatFile($path) {
    $application = new Application();
    $application->setAutoExit(false);

    if (!is_array($path)) {
        $path = [$path];
    }

    $input = new ArrayInput([
        "command" => "fix",
        "path" => $path,
        "--using-cache" => "no",
        "--quiet",
    ]);

    $output = new BufferedOutput();

    $application->run($input, $output);
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
];

parse($nodes);

// <?php
//
// $classNames = "foo bar";
// $message = "hello world";
//
// $thing = (
// );
