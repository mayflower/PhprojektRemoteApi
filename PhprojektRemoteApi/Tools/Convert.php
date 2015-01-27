<?php
/**
 * @copyright Copyright (c) 2015 Florian Eibeck
 * @license   THE BEER-WARE LICENSE (Revision 42)
 *
 * "THE BEER-WARE LICENSE" (Revision 42):
 * The author mentioned above wrote this software. As long as you retain this
 * notice you can do whatever you want with this stuff. If we meet some day,
 * and you think this stuff is worth it, you can buy us a beer in return.
 */

namespace PhprojektRemoteApi\Tools;

class Convert
{

    /**
     * Converts a time text from PHProjekt to hours
     * Example text: 111 : 45
     * or
     * 111 h 20 m
     *
     * @param string $text
     *
     * @return float
     */
    static public function text2hours($text)
    {
        $matches = array();
        if (preg_match('/^(\d*) : (\d*)$/', $text, $matches)) {
            return round($matches[1] + ($matches[2] / 60), 2);
        }
        if (preg_match('/^(\d*) h (\d*) m$/', $text, $matches)) {
            return round($matches[1] + ($matches[2] / 60), 2);
        }
        if (preg_match('/^- (\d*) h *(\d*) m$/', $text, $matches)) {
            return round(($matches[1] + ($matches[2] / 60)) * -1, 2);
        }
    }

}
 