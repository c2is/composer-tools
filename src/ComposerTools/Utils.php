<?php
/**
 * Created by JetBrains PhpStorm.
 * User: splancon
 * Date: 25/06/13
 * Time: 17:59
 * To change this template use File | Settings | File Templates.
 */

namespace ComposerTools;

class Utils {

    /**
     * Colorize text for shell output
     *
     * @param string $color name color, must be a key of array $colors that define in function
     * @param string $text  text to colorize
     *
     * @return string
     */
    public static function colorize($color, $text)
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

}