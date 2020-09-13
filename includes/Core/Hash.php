<?php
/**
 * Class Hash.
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
 * Class Hash
 *
 * Generate password and hash functions.
 */
class Hash
{
    /**
     * @var integer Password hash cost.
     */
    private static $cost = 12;

  /**
   * Generate a sha256 salted hash of a string.
   *
   * @param string $string String to hash.
   *
   * @return string
   */
    public static function generateHash(string $string)
    {
        $options = [
            'cost' => self::$cost
        ];
        return password_hash($string, PASSWORD_BCRYPT, $options);
    }

  /**
   * Verify the password matches the hash.
   *
   * @param string $password Password.
   * @param string $hash Hash.
   *
   * @return boolean
   */
    public function verifPassword(string $password, string $hash)
    {
        return password_verify($password, $hash);
    }

  /**
   * Generate a unique random token, based on a string.
   *
   * @param string $string String to create a token from.
   *
   * @return string
   */
    public static function generateToken(string $string)
    {
        return md5(time() . $string);
    }
}
