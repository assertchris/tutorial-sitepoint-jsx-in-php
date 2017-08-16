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

    while ($cursor < $length) {
        $extract = trim(substr($code, $cursor, 5)) . "...";

        if ($code[$cursor] === "{" && $elementStarted !== null) {
            if ($attributeLevel === 0) {
                print "ATTRIBUTE STARTED ({$cursor}, {$extract})" . PHP_EOL;
                $attributeStarted = $cursor;
            }

            $attributeLevel++;
        }

        if ($code[$cursor] === "}" && $elementStarted !== null) {
            $attributeLevel--;

            if ($attributeLevel === 0) {
                print "ATTRIBUTE ENDED ({$cursor})" . PHP_EOL;
                $attributeEnded = $cursor;
            }
        }

        preg_match("#^</?[a-zA-Z]#", substr($code, $cursor, 3), $matchesStart);

        if (count($matchesStart) && $attributeLevel < 1) {
            print "ELEMENT STARTED ({$cursor}, {$extract})" . PHP_EOL;

            $elementLevel++;
            $elementStarted = $cursor;
        }

        preg_match("#^=>#", substr($code, $cursor - 1, 2), $matchesEqualBefore);
        preg_match("#^>=#", substr($code, $cursor, 2), $matchesEqualAfter);

        if ($code[$cursor] === ">" && !$matchesEqualBefore && !$matchesEqualAfter && $attributeLevel < 1) {
            print "ELEMENT ENDED ({$cursor})" . PHP_EOL;

            $elementLevel--;
            $elementEnded = $cursor;
        }

        if ($elementStarted && $elementEnded) {
            // TODO

            $elementStarted = null;
            $elementEnded = null;
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

// ELEMENT STARTED (95, <div...)
// ATTRIBUTE STARTED (122, {() =...)
// ATTRIBUTE ENDED (152)
// ATTRIBUTE STARTED (173, {<spa...)
// ATTRIBUTE ENDED (222)
// ELEMENT ENDED (232)
// ELEMENT STARTED (279, <span...)
// ELEMENT ENDED (284)
// ELEMENT STARTED (350, </spa...)
// ELEMENT ENDED (356)
// ELEMENT STARTED (398, </div...)
// ELEMENT ENDED (403)
