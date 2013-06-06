<?php
/**
 * Created by JetBrains PhpStorm.
 * User: splancon
 * Date: 30/05/13
 * Time: 17:21
 * To change this template use File | Settings | File Templates.
 */

if ($argc < 2) {
    echo "Veuillez indiquer le chemin du fichier composer.json.\n";
    exit();
}

$projectDir = $argv[1];

if (!file_exists($argv[1].'composer.json')) {
    echo "Le fichier composer.json est introuvable.\n";
    exit();
}

$composerConf = json_decode(file_get_contents($projectDir.'composer.json'));

$minimumStability = "minimum-stability";
$minimumStability = $composerConf->$minimumStability;
switch ($minimumStability) {
    case 'stable':
        $colorizedMinimumStability = colorize('Green', $minimumStability);
        break;
    case 'dev':
        $colorizedMinimumStability = colorize('Red', $minimumStability);
        break;
    default:
        $colorizedMinimumStability = $minimumStability;
        break;
}
echo 'Search for minimum-visibility : '.$colorizedMinimumStability."\n";

$requires = "require";
$requires = $composerConf->$requires;

$availablesUpdates = array();

foreach ($requires as $package => $currentVersion) {
    if ($package == "php") {
        continue;
    }

    echo "Searching update for ".colorize('Green', $package).PHP_EOL;

    // No check if 'dev-master'
    if ($currentVersion == "dev-master") {
        echo "No update need for ".colorize('BWhite', $currentVersion).PHP_EOL.PHP_EOL;
        continue;
    }

    $tmpVersions = preg_split('`,`', $currentVersion);
    $minVersion = $tmpVersions[0];

    if (count($tmpVersions) > 1) {
        $maxVersion = $tmpVersions[1];
    } else {
        $maxVersion = $minVersion;
    }
    // No check if '>' or '>=' operators are presents
    if (substr($maxVersion, 0, 1) == '>') {
        echo "No update need for ".colorize('BWhite', $currentVersion).PHP_EOL.PHP_EOL;
        continue;
    }
    echo "Current max version : ".colorize('Yellow', $maxVersion).PHP_EOL.PHP_EOL;


    // Création du tableau de version
    preg_match('`^([<=>!~]*)([0-9]*).([0-9*-]*).([0-9*-]*)`', $maxVersion, $cvDetails);

    if ($cvDetails[4] == '*' || $cvDetails[4] == '') {
        $cvDetails[3] = (string) ($cvDetails[3] + 1);
        $cvDetails[4] = "0";
    } else {
        $cvDetails[4] = (string) ($cvDetails[4] + 1);
    }

    $minVersionToUpdate = implode('.', array_slice($cvDetails, 2, count($cvDetails)-2));
    switch ($minimumStability) {
        case 'dev':
            $minVersionToUpdate .= '-dev';
            break;
    }

    $cmdShowResult = `composer show $package | grep 'versions'`;
    preg_match('`versions(\033\[0m)* : (.*)`', $cmdShowResult, $availablesVersions);
    $availablesVersions = preg_split('`,`', $availablesVersions[2]);

    $matchesVersions = array();
    foreach ($availablesVersions as $av) {
        $av = preg_replace('`^v`', '', trim($av));
        switch ($minimumStability) {
            case 'stable':
                if (preg_match('`-dev$`', $av)) {
                    break;
                }
            case 'dev':
                if (version_compare(preg_replace('`x`', 0, $av), $minVersionToUpdate, '>=')) {
                    $availablesUpdates[$package] = $av;
                    break 2;
                }
                break;
        }
    }
}

$requiresDev = "require-dev";
$requiresDev = $composerConf->$requiresDev;

if (count($availablesUpdates) == 0) {
    echo colorize('BWhite', "No update found for your dependancies.".PHP_EOL);
} else {
    echo colorize('BWhite', "Summary of available update for ").$colorizedMinimumStability.colorize('BWhite', " stability")."\n";
    foreach ($availablesUpdates as $package => $au) {
        echo "Update found for ".colorize('Green', $package).": last available version is ".colorize('Yellow', $au).PHP_EOL;
    }
}
//var_dump($availablesUpdates);

function colorize($color, $text) {
    $colors = array(
        'close' => "\033[0m",
        'Green' => "\033[0;32m",
        'Red' => "\033[0;31m",
        'Yellow' => "\033[0;33m",
        'BYellow' => "\033[1;33m",
        'BWhite' => "\033[1;37m",
    );

    return $colors[$color].$text.$colors["close"];
}