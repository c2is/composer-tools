<?php
/**
 * Check new versions of dependancies
 *
 * @author  Sylvain PLANCON <sylvain.plancon@c2is.fr>
 * @package composer-tools by C2IS
 */

// Load languages
if (isset($argv[2])) {
    if (file_exists('locales/'.$argv[2].'.json')) {
        $translations = json_decode(file_get_contents('locales/'.$argv[2].'.json'), true);
    } else {
        $translations = json_decode(file_get_contents('locales/en.json'), true);
//        echo $translations['no-translation-found'].PHP_EOL;
    }
} else {
    $userLanguage = Locale::getPrimaryLanguage($_SERVER['LANG']);
    if (file_exists('locales/'.$userLanguage.'.json')) {
        $translations = json_decode(file_get_contents('locales/'.$userLanguage.'.json'), true);
//        echo $translations['use-system-language-for-translation'].PHP_EOL;
    } else {
        $translations = json_decode(file_get_contents('locales/en.json'), true);
//        echo $translations['no-translation-specified'].PHP_EOL;
    }
}

if ($argc < 2) {
    echo $translations['no-directory'].PHP_EOL;
    exit();
}

$projectDir = $argv[1];

if (!file_exists($argv[1].'composer.json')) {
    echo $translations['no-composer'].PHP_EOL;
    exit();
}

$composerConf = json_decode(file_get_contents($projectDir.'composer.json'));

$minimumStability = "minimum-stability";
switch ($composerConf->$minimumStability) {
    case 'stable':
        $colorizedMinimumStability = colorize('Green', $composerConf->$minimumStability);
        break;
    case 'dev':
        $colorizedMinimumStability = colorize('Red', $composerConf->$minimumStability);
        break;
    default:
        $colorizedMinimumStability = $composerConf->$minimumStability;
        break;
}
echo sprintf($translations['search-stability'] ,$colorizedMinimumStability).PHP_EOL;

$requires = "require";

$availableUpdates = array();
$availableDevUpdates = array();

echo sprintf($translations['check-for-section'], colorize('BWhite', "require")).PHP_EOL;
foreach ($composerConf->$requires as $package => $currentVersion) {
    // Exclude php, extensions end libraries
    if (preg_match('`^php$|^ext-|^lib-`', $package)) {
        continue;
    }

    $tmpRequire = check_version($package, $currentVersion, $composerConf->$minimumStability);

    if ($tmpRequire !== null) {
        $availableUpdates[$tmpRequire['name']] = $tmpRequire['version'];
    }
}

$requiresDev = "require-dev";
if (isset($composerConf->$requiresDev)) {

    echo sprintf($translations['check-for-section'], colorize('BWhite', $requiresDev)).PHP_EOL;
    foreach ($composerConf->$requiresDev as $package => $currentVersion) {
        if ($package == "php") {
            continue;
        }

        $tmpRequire = check_version($package, $currentVersion, $composerConf->$minimumStability);
        if ($tmpRequire !== null) {
            $availableDevUpdates[$tmpRequire['name']] = $tmpRequire['version'];
        }
    }
}

if (count($availableUpdates) == 0) {
    echo colorize('BWhite', sprintf($translations['no-require-update-found'])).PHP_EOL;
} else {
    echo colorize('BWhite', $translations['summary-title']).PHP_EOL;
    echo colorize('BWhite', sprintf($translations['summary-section'], colorize('Green', $requires))).PHP_EOL;
    echo colorize('BWhite', sprintf($translations['summary-stability'], $colorizedMinimumStability)).PHP_EOL;
    foreach ($availableUpdates as $package => $au) {
        echo sprintf($translations['update-available-for'], colorize('Green', $package), colorize('Yellow', $au)).PHP_EOL;
    }
}

if (isset($composerConf->$requiresDev) && count($composerConf->$requiresDev) > 0) {
    echo PHP_EOL;
    if (count($availableDevUpdates) == 0) {
        echo colorize('BWhite', sprintf($translations['no-require-dev-update-found'])).PHP_EOL;
    } else {
        echo colorize('BWhite', $translations['summary-title']).PHP_EOL;
        echo colorize('BWhite', sprintf($translations['summary-section'], colorize('Yellow', $requiresDev))).PHP_EOL;
        echo colorize('BWhite', sprintf($translations['summary-stability'], $colorizedMinimumStability)).PHP_EOL;
        foreach ($availableDevUpdates as $package => $au) {
            echo sprintf($translations['update-available-for'], colorize('Green', $package), colorize('Yellow', $au)).PHP_EOL;
        }
    }
}

/**
 * Colorize text for shell output
 *
 * @param string $color name color, must be a key of array $colors that define in function
 * @param string $text  text to colorize
 *
 * @return string
 */
function colorize($color, $text)
{
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

/**
 * Check version for a package and return newer version if greater than a specific version.
 *
 * @param string $package          Package name
 * @param string $currentVersion   reference's version
 * @param string $minimumStability minimum stability to consider
 *
 * @return array|null
 */
function check_version($package, $currentVersion, $minimumStability)
{
    global $translations;

    echo sprintf($translations['search-for'], colorize('Green', $package)).PHP_EOL;

    // No check if 'dev-master'
    if ($currentVersion == "dev-master") {
        echo sprintf($translations['no-update-need'], colorize('BWhite', $currentVersion)).PHP_EOL.PHP_EOL;

        return null;
    }

    $tmpVersions = preg_split('`,`', $currentVersion);
    $minVersion = $tmpVersions[0];

    if (count($tmpVersions) > 1) {
        $maxVersion = $tmpVersions[1];
    } else {
        $maxVersion = $minVersion;
    }

    // No check if '>' or '>=' operators are presents
    if (substr($maxVersion, 0, 1) == '>' || $maxVersion == "*") {
        echo sprintf($translations['no-update-need'], colorize('BWhite', $currentVersion)).PHP_EOL.PHP_EOL;

        return null;
    }
    echo sprintf($translations['current-max-version'], colorize('Yellow', $maxVersion)).PHP_EOL.PHP_EOL;

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

    foreach ($availablesVersions as $av) {
        // Strip optional v before version number
        $av = preg_replace('`^v`', '', trim($av));
        switch ($minimumStability) {
            case 'stable':
                if (preg_match('`-dev$`', $av)) {
                    break;
                }
            case 'dev':
                if (version_compare(preg_replace('`x`', 0, $av), $minVersionToUpdate, '>=')) {
                    return array('name' => $package, 'version' => $av);
                }
                break;
        }
    }

    return null;
}
