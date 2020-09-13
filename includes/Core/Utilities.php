<?php
/**
 * Class Utilities.
 *
 * @package Gaterdata
 * @subpackage Core
 * @author john89
 * @copyright 2020-2030 GaterData
 * @license This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *      If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link https://gaterdata.com
 */

namespace Gaterdata\Core;

/**
 * Class Utilities
 *
 * Global utilities.
 */
class Utilities
{
    /**
     * @var string Lower case characters.
     */
    public static $lower_case = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @var string Upper case characters.
     */
    public static $upper_case = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @var string Real numbers.
     */
    public static $number = '0123456789';

    /**
     * @var string Special characters.
     */
    public static $special = '!@#$%^&*()';

    /**
     * Returns system time in micro secs.
     *
     * @return float
     **/
    public static function getMicrotime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Creates a random string, of a specified length.
     *
     * Contents of string specified by $lower, $upper, $number and $non_alphanum.
     *
     * @param integer $length Length of the string.
     * @param boolean $lower Include lower case alpha.
     * @param boolean $upper Include upper case alpha.
     * @param boolean $number Include integers.
     * @param boolean $special Include special characters.
     *
     * @return string
     **/
    public static function randomString(
        int $length = null,
        bool $lower = null,
        bool $upper = null,
        bool $number = null,
        bool $special = null
    ) {
        $length = empty($length) ? 8 : $length;
        $lower = empty($lower) ? true : $lower;
        $upper = empty($upper) ? true : $upper;
        $number = empty($number) ? true : $number;
        $special = empty($special) ? false : $special;
        $chars = '';
        if ($lower) {
            $chars .= self::$lower_case;
        }
        if ($upper) {
            $chars .= self::$upper_case;
        }
        if ($number) {
            $chars .= self::$number;
        }
        if ($special) {
            $chars .= self::$special;
        }

        $str = '';
        $count = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $count - 1)];
        }

        return $str;
    }

    /**
     * Converts php date to standard mysql date.
     *
     * @param integer $phpdate Unix time stamp.
     *
     * @return string
     **/
    public static function datePhp2mysql(int $phpdate)
    {
        return date('Y-m-d H:i:s', $phpdate);
    }

    /**
     * Converts mysql date to standard php date.
     *
     * @param integer $mysqldate Unix time stamp.
     *
     * @return string
     **/
    public static function dateMysql2php(int $mysqldate)
    {
        return strtotime($mysqldate);
    }

    /**
     * Create current standard mysql date
     *
     * @return string
     */
    public static function mysqlNow()
    {
        return self::datePhp2mysql(time());
    }

    /**
     * Check to see if $m_array is an associative array.
     *
     * @param mixed $m_array Mixed array.
     *
     * @return boolean
     **/
    public static function isAssoc($m_array)
    {
        if (!is_array($m_array)) {
            return false;
        }
        return array_keys($m_array) !== range(0, count($m_array) - 1);
    }

    /**
     * Obtain user IP even if they're under a proxy.
     *
     * @return string ip address
     *  IP address of the user
     */
    public static function getUserIP()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $proxy = $_SERVER["HTTP_X_FORWARDED_FOR"];
        if (preg_match("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $proxy)) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        return $ip;
    }

    /**
     * Get the current URL.
     *
     * @param boolean $returnArray Return in array format.
     *
     * @return array|string
     */
    public static function selfURL(bool $returnArray = null)
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = self::strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
        $port = (($_SERVER["SERVER_PORT"] == 80) ? '' : ':' . $_SERVER["SERVER_PORT"]);
        $address = $_SERVER['SERVER_NAME'];
        $uri = $_SERVER['REQUEST_URI'];

        if (!$returnArray) {
            return $protocol . '://' . $address . (($port == 80) ? '' : ":$port") . $uri;
        }
        $ret_array = array('protocol' => $protocol, 'port' => $port, 'address' => $address, 'uri' => $uri);
        return $ret_array;
    }

    /**
     * Return the character left of a substring win a string.
     *
     * @param string $s1 String.
     * @param string $s2 Substring.
     *
     * @return string substring left of $s2
     */
    public static function strleft(string $s1, string $s2)
    {
        return substr($s1, 0, strpos($s1, $s2));
    }

    /**
     * Redirect to current url under https, if under http.
     *
     * @return void
     */
    public static function makeUrlSecure()
    {
        $a_selfURL = self::selfURL(true);
        if ($a_selfURL['protocol'] == 'http') {
            header('Location: ' . $a_selfURL['protocol']. 's://'
                . $a_selfURL['address'] . $a_selfURL['port'] . $a_selfURL['uri']);
            exit();
        }
    }

    /**
     * Redirect to current url under http, if under https.
     *
     * @return void
     */
    public static function makeUrlInsecure()
    {
        $a_selfURL = self::selfURL(true);
        if ($a_selfURL['protocol'] == 'https') {
            header('Location: http://' . $a_selfURL['address'] . $a_selfURL['uri']);
            exit();
        }
    }

    /**
     * Check if a url exists.
     *
     * @param string $url The URL.
     *
     * @return boolean
     */
    public static function doesUrlExist(string $url)
    {
        $headers = @get_headers($url);
        if (strpos($headers[0], '200') === false) {
            return false;
        }
        return true;
    }

    /**
     * Check if current url is https.
     *
     * @return boolean
     */
    public static function isSecure()
    {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
            || !empty($_SERVER['HTTP_X_FORWARDED_SSL'])
            && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        return $isSecure;
    }

    /**
     * Recursively set access rights on a directory.
     *
     * @param string $dir Directory string.
     * @param integer $dirAccess Directory permission to set.
     * @param integer $fileAccess File permission to set.
     * @param array $nomask Nomask permission to set.
     *
     * @return void
     */
    public static function setAccessRights(
        string $dir,
        int $dirAccess = null,
        int $fileAccess = null,
        array $nomask = array('.', '..')
    ) {
        $dirAccess = empty($dirAccess) ? 0777 : $dirAccess;
        $fileAccess = empty($dirAccess) ? 0666 : $dirAccess;
        //error_log("Make writable: $dir");
        if (is_dir($dir)) {
            // Try to make each directory world writable.
            if (@chmod($dir, $dirAccess)) {
                error_log("Make writable: $dir");
            }
        }
        if (is_dir($dir) && $handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $nomask) && $file[0] != '.') {
                    if (is_dir("$dir/$file")) {
                        // Recurse into subdirectories
                        self::setAccessRights("$dir/$file", $dirAccess, $fileAccess, $nomask);
                    } else {
                        $filename = "$dir/$file";
                        // Try to make each file world writable.
                        if (@chmod($filename, $fileAccess)) {
                            error_log("Make writable: $filename");
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}
