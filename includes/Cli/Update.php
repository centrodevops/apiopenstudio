<?php

/**
 * Class Update.
 *
 * @package    ApiOpenStudio
 * @subpackage Cli
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 ApiOpenStudio
 * @license    This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *             If a copy of the MPL was not distributed with this file,
 *             You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Cli;

use ApiOpenStudio\Core\ApiException;
use ApiOpenStudio\Core\Config;
use ApiOpenStudio\Db;
use Berlioz\PhpDoc\PhpDocFactory;

/**
 * Class Install
 *
 * Script to update the ApiOpenStudio database.
 */
class Update extends Script
{
    /**
     * @var string Relative path to updates directory.
     */
    protected $updateDir = 'includes/updates/';

    /**
     * {@inheritDoc}
     */
    protected $argMap = [
        'options' => [
            'd' => [
                'required' => false,
                'multiple' => false,
            ],
        ],
        'flags' => [],
    ];

    /**
     * @var Config Config class.
     */
    protected $config;

    /**
     * @var ADODB_mysqli database connection.
     */
    protected $db;

    /**
     * @var string Last update run.
     */
    protected $lastUpdateVersionRun;

    /**
     * Install constructor.
     */
    public function __construct()
    {
        $this->config = new Config();
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function help()
    {
        $help = "Update\n\n";
        $help .= "This command will update the database.\n";
        $help .= "It will run all necessary core update functions in includes/updates.\n\n";
        $help .= "Options\n";
        $help .= "-d: (optional) full path to the update directory containing the update files.\n\n";
        $help .= "Examples:\n";
        $help .= "./include/scripts/update.php\n";
        $help .= "./include/scripts/update.php -d ./foobar\n";
        $help .= "./include/scripts/update.php -d ./foobar/\n";
        echo $help;
    }

    /**
     * {@inheritDoc}
     */
    public function exec(array $argv = null)
    {
        parent::exec($argv);
        // Override the default update directory if option -d is input.
        if (!empty($this->options['d'])) {
            $this->updateDir = $this->options['d'];
        } else {
            $this->updateDir = $this->config->__get(['api', 'base_path']) . $this->updateDir;
        }
        if (substr($this->updateDir, -1) != '/') {
            $this->updateDir = $this->updateDir . '/';
        }

        $response = '';
        while ($response != 'y' && $response != 'n') {
            $prompt = 'Continuing will update database. It is recommended to make a backup beforehand. ';
            $prompt .= 'Continue [Y/n]: ';
            $response = $this->readlineTerminal($prompt);
            $response = empty($response) ? 'y' : strtolower($response);
        }
        if ($response != 'y') {
            echo "Exiting update...\n";
            exit;
        }

        $this->setupDBLink();

        $currentVersion = $this->getCurrentVersion();
        echo "\n";
        $functions = $this->findUpdates($currentVersion);
        echo "\n";
        if (empty($functions)) {
            echo "No updates to run!\n";
            exit;
        }
        $this->runUpdates($functions);
        echo "\n";
    }

    /**
     * Setup the DB connection.
     *
     * @throws ApiException
     */
    protected function setupDBLink()
    {
        $dsnOptionsArr = [];
        foreach ($this->config->__get(['db', 'options']) as $k => $v) {
            $dsnOptionsArr[] = "$k=$v";
        }
        $dsnOptions = count($dsnOptionsArr) > 0 ? ('?' . implode('&', $dsnOptionsArr)) : '';
        $dsn = $this->config->__get(['db', 'driver']) . '://'
            . $this->config->__get(['db', 'username']) . ':'
            . $this->config->__get(['db', 'password']) . '@'
            . $this->config->__get(['db', 'host']) . '/'
            . $this->config->__get(['db', 'database'])
            . $dsnOptions;
        if (!$this->db = \ADONewConnection($dsn)) {
            echo "Error: DB connection failed, please check your settings.yml file.\n";
            exit;
        }
    }

    protected function getCurrentVersion()
    {
        echo "Finding current version...\n";
        $sql = 'SELECT version FROM core';
        $row = $this->db->GetRow($sql);
        $version = $row['version'];
        if (empty($version)) {
            echo "Could not find current version, exiting...\n";
            exit;
        }
        echo "Current version: $version\n";
        $this->lastUpdateVersionRun = $version;
        return $version;
    }

    /**
     * Find all update functions.
     *
     * @param string $currentVersion
     *   Current version.
     *
     * @return array
     *   All functions with meta.
     * @return array
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    protected function findUpdates(string $currentVersion)
    {
        echo "Scanning " . $this->updateDir . " for updates...\n";
        $currentVersion = trim(str_ireplace('v', '', $currentVersion));
        $phpDocFactory = new PhpDocFactory();
        $files = glob($this->updateDir . '*.php');
        $result = [];

        foreach ($files as $file) {
            include $file;
            $functions = $this->getDefinedFunctionsInFile($file);
            if (empty($functions)) {
                echo "Error: no updates found in $file\n";
                exit;
            }
            foreach ($functions as $function) {
                $docblock = $phpDocFactory->getFunctionDoc($function);
                if (!$docblock->hasTag('version')) {
                    echo "Error: No version found in the PHPDoc in $function\n";
                    exit;
                }
                $version = $docblock->getTag('version')[0]->getValue();
                if (!preg_match("/([vV])+\\s?([0-9]\\.){2}[0-9]/", $version)) {
                    echo "Error: Invalid version found in the PHPDoc in $function\n";
                    exit;
                }
                $version = trim(str_ireplace('v', '', $version));
                if ($this->sortByVersion($version, $currentVersion) > 0) {
                    $result[$function] = $version;
                }
            }
        }

        if (!uasort($result, [$this, 'sortByVersion'])) {
            echo "Error: Failed to sort the update functions\n";
            exit;
        }

        return $result;
    }

    protected function runUpdates($functions)
    {
        foreach ($functions as $function => $version) {
            echo "Running update $function\n";
            $function($this->db);
            if ($this->sortByVersion($version, $this->lastUpdateVersionRun) > 0) {
                $this->lastUpdateVersionRun = $version;
                $sql = 'UPDATE core SET version="' . $version . '"';
                if (!$this->db->execute($sql)) {
                    echo "Error: failed to update core version to $version";
                    exit;
                }
            }
        }
    }

    /**
     * List ann functions defined in a file.
     *
     * @param string $file
     *   Path to the file.
     *
     * @return array
     *   Array of function names.
     */
    protected function getDefinedFunctionsInFile($file)
    {
        $source = file_get_contents($file);
        $tokens = token_get_all($source);

        $functions = array();
        $nextStringIsFunc = false;
        $inClass = false;
        $bracesCount = 0;

        foreach ($tokens as $token) {
            switch ($token[0]) {
                case T_CLASS:
                    $inClass = true;
                    break;

                case T_FUNCTION:
                    if (!$inClass) {
                        $nextStringIsFunc = true;
                    }
                    break;

                case T_STRING:
                    if ($nextStringIsFunc) {
                        $nextStringIsFunc = false;
                        $functions[] = $token[1];
                    }
                    break;

                    // Anonymous functions
                case '(':
                case ';':
                    $nextStringIsFunc = false;
                    break;

                    // Exclude Classes
                case '{':
                    if ($inClass) {
                        $bracesCount++;
                    }
                    break;

                case '}':
                    if ($inClass) {
                        $bracesCount--;
                        if ($bracesCount === 0) {
                            $inClass = false;
                        }
                    }
                    break;
            }
        }

        return $functions;
    }

    /**
     * Custom sort function to sort array oif cun
     *
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    public function sortByVersion($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        $a = explode('.', $a);
        $b = explode('.', $b);
        if ($a[0] < $b[0]) {
            return -1;
        } elseif ($a[0] > $b[0]) {
            return 1;
        }
        if ($a[1] < $b[1]) {
            return -1;
        } elseif ($a[1] > $b[1]) {
            return 1;
        }
        if ($a[2] < $b[2]) {
            return -1;
        }
        return 1;
    }
}
