<?php
/**
 * Check new versions of dependancies
 *
 * @author  Sylvain PLANCON <sylvain.plancon@c2is.fr>
 * @package composer-tools by C2IS
 */

require_once __DIR__.'/vendor/autoload.php';

use ComposerTools\Utils;
use ComposerTools\ComposerTools;

$lang = (isset($argv[2])) ? $argv[2] : "";

// Initialize ComposerTools
$composerTools = new ComposerTools($lang);

if ($argc < 2) {
    echo $composerTools->translations['no-directory'].PHP_EOL;
    exit();
}

$projectDir = $argv[1];

if (!file_exists($argv[1].'composer.json')) {
    echo $composerTools->translations['no-composer'].PHP_EOL;
    exit();
}

// Loading composer.json
$composerTools->loadComposerFile($projectDir);
$minimumStability = $composerTools->getComposerConfig("minimum-stability");
switch ($minimumStability) {
    case 'stable':
        $colorizedMinimumStability = Utils::colorize('Green', $minimumStability);
        break;
    case 'dev':
        $colorizedMinimumStability = Utils::colorize('Red', $minimumStability);
        break;
    default:
        $colorizedMinimumStability = $minimumStability;
        break;
}
echo sprintf($composerTools->translations['search-stability'] ,$colorizedMinimumStability).PHP_EOL;

$availableUpdates = array();
$availableDevUpdates = array();

// Check available updates for 'require' section
echo sprintf($composerTools->translations['check-for-section'], Utils::colorize('BWhite', "require")).PHP_EOL;
foreach ($composerTools->getComposerConfig("require") as $package => $currentVersion) {
    // Exclude php, extensions end libraries
    if (preg_match('`^php$|^ext-|^lib-`', $package)) {
        continue;
    }

    $tmpRequire = $composerTools->check_version($package, $currentVersion, $minimumStability);

    if ($tmpRequire !== null) {
        $availableUpdates[$tmpRequire['name']] = $tmpRequire['version'];
    }
}

// Check available updates for 'require-dev' section if exist
if ($composerTools->getComposerConfig("require-dev"))
{
    echo sprintf($composerTools->translations['check-for-section'], Utils::colorize('BWhite', "require-dev")).PHP_EOL;
    foreach ($composerTools->getComposerConfig("require-dev") as $package => $currentVersion) {
        // Exclude php, extensions end libraries
        if (preg_match('`^php$|^ext-|^lib-`', $package)) {
            continue;
        }

        $tmpRequire = $composerTools->check_version($package, $currentVersion, $minimumStability);
        if ($tmpRequire !== null) {
            $availableDevUpdates[$tmpRequire['name']] = $tmpRequire['version'];
        }
    }
}

// Show report for 'require' section
if (count($availableUpdates) == 0) {
    echo Utils::colorize('BWhite', sprintf($composerTools->translations['no-require-update-found'])).PHP_EOL;
} else {
    echo Utils::colorize('BWhite', $composerTools->translations['summary-title']).PHP_EOL;
    echo Utils::colorize('BWhite', sprintf($composerTools->translations['summary-section'], Utils::colorize('Green', "require"))).PHP_EOL;
    echo Utils::colorize('BWhite', sprintf($composerTools->translations['summary-stability'], $colorizedMinimumStability)).PHP_EOL;
    foreach ($availableUpdates as $package => $au) {
        echo sprintf($composerTools->translations['update-available-for'], Utils::colorize('Green', $package), Utils::colorize('Yellow', $au)).PHP_EOL;
    }
}

// Show report for 'rquire-dev' section
if ($composerTools->getComposerConfig("require-dev") && count($composerTools->getComposerConfig("require-dev")) > 0) {
    echo PHP_EOL;
    if (count($availableDevUpdates) == 0) {
        echo Utils::colorize('BWhite', sprintf($composerTools->translations['no-require-dev-update-found'])).PHP_EOL;
    } else {
        echo Utils::colorize('BWhite', $composerTools->translations['summary-title']).PHP_EOL;
        echo Utils::colorize('BWhite', sprintf($composerTools->translations['summary-section'], Utils::colorize('Yellow', "require-dev"))).PHP_EOL;
        echo Utils::colorize('BWhite', sprintf($composerTools->translations['summary-stability'], $colorizedMinimumStability)).PHP_EOL;
        foreach ($availableDevUpdates as $package => $au) {
            echo sprintf($composerTools->translations['update-available-for'], Utils::colorize('Green', $package), Utils::colorize('Yellow', $au)).PHP_EOL;
        }
    }
}
