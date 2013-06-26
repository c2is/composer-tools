<?php
/**
 *
 */

namespace ComposerTools;

class ComposerTools {

    public  $translations = array();
    private $lang = 'en';
    private $conf;

    public function __construct($language)
    {
        // Load translations
        if (isset($language)) {
            if (file_exists('locales/'.\Locale::getPrimaryLanguage($language).'.json')) {
                $this->lang = \Locale::getPrimaryLanguage($language);
            }
        } else {
            $userLanguage = \Locale::getPrimaryLanguage($_SERVER['LANG']);
            if (file_exists('locales/'.$userLanguage.'.json')) {
                $this->lang = $userLanguage;
            }
        }
        $this->translations = json_decode(file_get_contents(__DIR__.'/locales/'.$this->lang.'.json'), true);
    }

    public function loadComposerFile($pathToFile)
    {
        $this->conf = json_decode(file_get_contents($pathToFile.'composer.json'));
    }

    public function getComposerConfig($config)
    {
        if (!is_string($config) || !isset($this->conf->$config))
        {
            return null;
        }

        return $this->conf->$config;
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
    public function check_version($package, $currentVersion, $minimumStability)
    {
        echo sprintf($this->translations['search-for'], Utils::colorize('Green', $package)).PHP_EOL;

        // No check if 'dev-master'
        if ($currentVersion == "dev-master") {
            echo sprintf($this->translations['no-update-need'], Utils::colorize('BWhite', $currentVersion)).PHP_EOL.PHP_EOL;

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
            echo sprintf($this->translations['no-update-need'], Utils::colorize('BWhite', $currentVersion)).PHP_EOL.PHP_EOL;

            return null;
        }
        echo sprintf($this->translations['current-max-version'], Utils::colorize('Yellow', $maxVersion)).PHP_EOL.PHP_EOL;

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

}