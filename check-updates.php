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
echo 'Search for minimum-visibility : '.$minimumStability."\n";

$requires = "require";
$requires = $composerConf->$requires;

$$availablesUpdates = array();

foreach ($requires as $package => $currentVersion) {
    if ($package == "php") {
        continue;
    }

    echo "Searching update for ".$package."\n";
    echo "Current version : ".$currentVersion."\n\n";

    // Création du tableau de version
    preg_match('`^([0-9]*).([0-9*]*).([0-9*]*)`', $currentVersion, $cvDetails);
//    var_dump($cvDetails);

    $cmdShowResult = `composer show $package`;
    preg_match('`versions : (.*)\n`', $cmdShowResult, $availablesVersions);
    $availablesVersions = preg_split('`,`', $availablesVersions[1]);

    $matchesVersions = array();
    foreach ($availablesVersions as $av) {
        switch ($minimumStability) {
            case 'dev':
                if (preg_match('`'.$minimumStability.'$`', $av)) {
                    $matchesVersions[] = trim($av);
                    foreach ($matchesVersions as $mv) {
                        preg_match('`^([0-9]*).([0-9*]*).([0-9*]*)`', $mv, $mvDetails);
                        if ($mvDetails[1] > $cvDetails[1]) {
                            $$availablesUpdates[$package] = $mv;
                            break 2;
                        }
                        if ($cvDetails[2] != '*' && $mvDetails[2] > $cvDetails[2]) {
                            $$availablesUpdates[$package] = $mv;
                            break 2;
                        }
                        if ($cvDetails[3] != '*' && $mvDetails[3] > $cvDetails[3]) {
                            $$availablesUpdates[$package] = $mv;
                            break 2;
                        }
                    }
                }
                break;
        }
    }
//    var_dump($$availablesUpdates);
//
//    die();
//    echo $package." : version ".$version."\n";
}

$requiresDev = "require-dev";
$requiresDev = $composerConf->$requiresDev;

if (count($$availablesUpdates) == 0) {
    echo "No update found for your dependancies.\n";
} else {
    foreach ($$availablesUpdates as $package => $au) {
        echo "Update found for ".$package.": last available version is ".$au."\n";
    }
}
//var_dump($$availablesUpdates);