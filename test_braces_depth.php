<?php
$content = file_get_contents('packages/workdo/Lead/src/Entities/LeadStage.php');
$tokens = token_get_all($content);
$depth = 0;
$line = 1;
foreach ($tokens as $token) {
    if (is_array($token)) {
        $line = $token[2];
        if ($token[0] == T_CURLY_OPEN || $token[0] == T_DOLLAR_OPEN_CURLY_BRACES) {
            $depth++;
            echo "Line $line: Open brace, depth: $depth\n";
        }
        if ($token[0] == T_FUNCTION) {
            echo "Line $line: FUNCTION definition found at depth $depth\n";
        }
    }
    else if (is_string($token)) {
        if ($token === '{') {
            $depth++;
            echo "Line $line: Open brace, depth: $depth\n";
        }
        if ($token === '}') {
            $depth--;
            echo "Line $line: Close brace, depth: $depth\n";
        }
    }
}
