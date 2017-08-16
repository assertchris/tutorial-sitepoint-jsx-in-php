<?php

function tokens($code) {
    $tokens = [];

    $length = strlen($code);
    $cursor = 0;

    while ($cursor < $length) {
        if ($code[$cursor] === "{") {
            print "ATTRIBUTE STARTED ({$cursor})" . PHP_EOL;
        }

        if ($code[$cursor] === "}") {
            print "ATTRIBUTE ENDED ({$cursor})" . PHP_EOL;
        }

        if ($code[$cursor] === "<") {
            print "ELEMENT STARTED ({$cursor})" . PHP_EOL;
        }

        if ($code[$cursor] === ">") {
            print "ELEMENT ENDED ({$cursor})" . PHP_EOL;
        }

        $cursor++;
    }
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

// ELEMENT STARTED (5)
// ELEMENT STARTED (95)
// ATTRIBUTE STARTED (122)
// ELEMENT ENDED (127)
// ATTRIBUTE STARTED (129)
// ATTRIBUTE ENDED (151)
// ATTRIBUTE ENDED (152)
// ATTRIBUTE STARTED (173)
// ELEMENT STARTED (174)
// ATTRIBUTE STARTED (190)
// ATTRIBUTE ENDED (204)
// ELEMENT ENDED (205)
// ELEMENT STARTED (215)
// ELEMENT ENDED (221)
// ATTRIBUTE ENDED (222)
// ELEMENT ENDED (232)
// ELEMENT STARTED (279)
// ELEMENT ENDED (284)
// ATTRIBUTE STARTED (302)
// ATTRIBUTE ENDED (311)
// ELEMENT STARTED (350)
// ELEMENT ENDED (356)
// ELEMENT STARTED (398)
// ELEMENT ENDED (403)
