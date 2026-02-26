<?php
$content = file_get_contents('packages/workdo/Lead/src/Entities/LeadStage.php');
$tokens = token_get_all($content);
$braces = [];
$line = 1;
foreach ($tokens as $token) {
    if (is_array($token)) {
        $line = $token[2];
        if ($token[0] == T_CURLY_OPEN || $token[0] == T_DOLLAR_OPEN_CURLY_BRACES) {
            $braces[] = $line;
        }
    }
    else if (is_string($token)) {
        if ($token === '{')
            $braces[] = $line;
        if ($token === '}') {
            if (empty($braces))
                echo "Unmatched closing brace at line $line\n";
            else
                array_pop($braces);
        }
    }
}
echo "Remaining open braces opened at lines: " . implode(', ', $braces) . "\n";
