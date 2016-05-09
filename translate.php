<?php
/**
 * YAML to XLF using Bing Translator
 *
 * @category    Translation
 * @author      CarcaBot
 */

set_time_limit(0);
ini_set('display_errors', 0);

require_once('translate.class.php');

$translate = new Translator();

if (count($argv) == 1) {
    echo "\033[32m php -q {$argv[0]} --from=en --to=es --client=clientID --secret=clientSecret\n"
    . " php -q {$argv[0]} --languages"
    . "\033[0m\n";
    exit();
}
unset($argv[0]);

if ($argv[1] == '--languages') {
    print_r($translate->getLanguages());
    die();
}
foreach ($argv as $arg) {
    list($k, $v) = explode("=", $arg);
    switch ($k) {
        case '--from':
            $translate->setFrom($v);
            break;
        case '--to':
            $translate->setTo($v);
            break;
        case '--client':
            $translate->setClient($v);
            break;
        case '--secret':
            $translate->setSecret($v);
            break;
    }
}

try {
    $translate->execute();
} catch (Exception $ex) {
    echo $ex->getMessage();
}






