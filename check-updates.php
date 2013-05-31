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

$requires = "require";
$requires = $composerConf->$requires;

foreach ($requires as $package => $version) {
    if ($package == "php") { continue; }
    echo $package." : version ".$version."\n";
}

$requires_dev = "require-dev";
$requires_dev = $composerConf->$requires_dev;

//var_dump($composerConf);