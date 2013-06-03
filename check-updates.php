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

$minimum_stability = "minimum-stability";
$minimum_stability = $composerConf->$minimum_stability;
echo 'Search for minimum-visibility : '.$minimum_stability."\n";

$requires = "require";
$requires = $composerConf->$requires;

$availables_updates = array();

foreach ($requires as $package => $current_version) {
    if ($package == "php") { continue; }

    echo "Searching update for ".$package."\n";
    echo "Current version : ".$current_version."\n\n";

    // Création du tableau de version
    preg_match('`^([0-9]*).([0-9*]*).([0-9*]*)`', $current_version, $cv_details);
//    var_dump($cv_details);

    $cmd_show_result = `composer show $package`;
    preg_match('`versions : (.*)\n`', $cmd_show_result, $availables_versions);
    $availables_versions = preg_split('`,`', $availables_versions[1]);

    $matches_versions = array();
    foreach ($availables_versions as $av) {
        switch ($minimum_stability) {
            case 'dev':
                if (preg_match('`'.$minimum_stability.'$`', $av)) {
                    $matches_versions[] = trim($av);
                    foreach ($matches_versions as $mv) {
                        preg_match('`^([0-9]*).([0-9*]*).([0-9*]*)`', $mv, $mv_details);
                        if ($mv_details[1] > $cv_details[1]) {
                            $availables_updates[$package] = $mv;
                            break 2;
                        }
                        if ($cv_details[2] != '*' && $mv_details[2] > $cv_details[2]) {
                            $availables_updates[$package] = $mv;
                            break 2;
                        }
                        if ($cv_details[3] != '*' && $mv_details[3] > $cv_details[3]) {
                            $availables_updates[$package] = $mv;
                            break 2;
                        }
                    }
                }
                break;
        }
    }
//    var_dump($availables_updates);
//
//    die();
//    echo $package." : version ".$version."\n";
}

$requires_dev = "require-dev";
$requires_dev = $composerConf->$requires_dev;

if (count($availables_updates) == 0) {
    echo "No update found for your dependancies.\n";
} else {
    foreach ($availables_updates as $package => $au) {
        echo "Update found for ".$package.": last available version is ".$au."\n";
    }
}
//var_dump($availables_updates);