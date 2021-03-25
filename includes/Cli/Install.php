<?php

/**
 * Class Install.
 *
 * @package    ApiOpenStudio
 * @subpackage Cli
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 *             If a copy of the MPL was not distributed with this file,
 *             You can obtain one at https://mozilla.org/MPL/2.0/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Cli;

use ApiOpenStudio\Core\ApiException;
use ApiOpenStudio\Core\Config;
use ApiOpenStudio\Db;

/**
 * Class Install
 *
 * Script to setup the ApiOpenStudio database.
 */
class Install extends Script
{
    /**
     * {@inheritDoc}
     */
    protected $argMap = [
        'options' => [],
        'flags' => [],
    ];

    /**
     * @var Config Config class.
     */
    protected $config;

    /**
     * @var ADONewConnection database connection.
     */
    protected $db;

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
        $help = "Install\n\n";
        $help .= "This command will create the database and install ApiOpenStudio.\n\n";
        $help .= "Example:\n";
        $help .= "./include/scripts/install.php\n";
        echo $help;
    }

    /**
     * Execute the function.
     *
     * @param array|null $argv
     *   CLI args.
     *
     * @return void
     */
    public function exec(array $argv = null)
    {
        parent::exec($argv);

        $response = '';
        while ($response != 'y' && $response != 'n') {
            $prompt = 'Continuing will create a new database and erase the current database, ';
            $prompt .= 'if it exists, continue [Y/n]: ';
            $response = $this->readlineTerminal($prompt);
            $response = empty($response) ? 'y' : strtolower($response);
        }
        if ($response != 'y') {
            echo "Exiting install...\n";
            exit;
        }

        $createDb = '';
        while (!is_bool($createDb)) {
            $prompt = "Recreate or create new database, user and user permissions (Y/n)?";
            $response = strtolower($this->readlineTerminal($prompt));
            $createDb = $response === 'y' || empty($response) ? true : $createDb;
            $createDb = $response === 'n' ? false : $createDb;
        }

        if ($createDb) {
            $this->createLink(null, null, '', 'root', $this->config->__get(['db', 'root_password']));
            echo "\n";
            $this->dropDatabase();
            echo "\n";
            $this->dropUser($this->config->__get(['db', 'username']));
            echo "\n";
            $this->createDatabase();
            echo "\n";
            $this->createUser();
            echo "\n";
        }
        $this->useDatabase();
        echo "\n";
        $this->createTables();
        echo "\n";
        $this->createResources();
        echo "\n";
        $this->createAdminUser();
        echo "\n";
    }

    /**
     * Connect to the database.
     *
     * @param string|null $driver
     *   Database driver.
     * @param string|null $host
     *   Database host.
     * @param string|null $database
     *   Database name.
     * @param string|null $username
     *   Database username.
     * @param string|null $password
     *   Database password.
     */
    public function createLink(
        string $driver = null,
        string $host = null,
        string $database = null,
        string $username = null,
        string $password = null
    ) {
        echo "Creating a connection the the database host...\n";

        try {
            $driver = $driver === null ? $this->config->__get(['db', 'driver']) : $driver;
            $host = $host === null ? $this->config->__get(['db', 'host']) : $host;
            $database = $database === null ? $this->config->__get(['db', 'database']) : $database;
            $username = $username === null ? $this->config->__get(['db', 'username']) : $username;
            $password = $password === null ? $this->config->__get(['db', 'password']) : $password;
        } catch (ApiException $e) {
            echo "Error: DB connection failed, please check your settings.yml file.\n";
            echo $e->getMessage();
            exit;
        }

        // DB link.
        $this->db = adoNewConnection($driver);
        if (empty($database)) {
            if (!$this->db->connect($host, $username, $password)) {
                echo "Error: DB connection failed.\n";
                echo $this->db->errorMsg() . "\n";
                exit;
            }
        } else {
            if (!$this->db->connect($host, $username, $password, $database)) {
                echo "Error: DB connection failed.\n";
                echo $this->db->errorMsg() . "\n";
                exit;
            }
        }

        echo "Connection successful!\n";
    }

    /**
     * Close the DB link.
     */
    public function closeLink()
    {
        echo "Closing connection the the database host...\n";

        if (!$this->db->close()) {
            echo "Error: Could not close the connection, please check the logs.\n";
            exit;
        }

        echo "Disconnect successful!\n";
    }

    /**
     * Create the database, user and permissions.
     *
     * @param string|null $database
     *   Database name to create.
     */
    public function createDatabase(string $database = null)
    {
        try {
            $database = $database === null ? $this->config->__get(['db', 'database']) : $database;
        } catch (ApiException $e) {
            echo "Error: Create `$database` database failed, please check your settings.yml file.\n";
            echo $e->getMessage();
            exit;
        }

        echo "Creating the `$database` Database...\n";

        $sql = "CREATE DATABASE IF NOT EXISTS `$database`";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Create `$database` database failed, please check the logs.\n";
            exit;
        }

        echo "Database `$database` created successfully!\n";
    }

    /**
     * Drop a database.
     *
     * @param string|null $database
     *   Database name to drop.
     */
    public function dropDatabase(string $database = null)
    {
        try {
            $database = $database === null ? $this->config->__get(['db', 'database']) : $database;
        } catch (ApiException $e) {
            echo "Error: Drop `$database` database failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        echo "Dropping the `$database` Database...\n";

        $sql = "DROP DATABASE IF EXISTS `$database`";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Drop database `$database` Failed, please check your logs.\n";
            exit;
        }

        echo "Database `$database` dropped successfully!\n";
    }

    /**
     * Use a database.
     *
     * @param string|null $database
     *   Name of the database.
     */
    public function useDatabase(string $database = null)
    {
        try {
            $database = $database === null ? $this->config->__get(['db', 'database']) : $database;
        } catch (ApiException $e) {
            echo "Error: use `$database` database failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        echo "Using the `$database` Database...\n";

        $sql = "USE `$database`";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Use database `$database` Failed, please check your logs.\n";
            exit;
        }

        echo "Using Database `$database` successful!\n";
    }

    /**
     * Create the database, user and permissions.
     *
     * @param string|null $database
     *   Database name.
     * @param string|null $username
     *   Database username to create.
     * @param string|null $password
     *   Database password to create.
     */
    public function createUser(string $database = null, string $username = null, string $password = null)
    {
        try {
            $database = $database === null ? $this->config->__get(['db', 'database']) : $database;
            $username = $username === null ? $this->config->__get(['db', 'username']) : $username;
            $password = $password === null ? $this->config->__get(['db', 'password']) : $password;
        } catch (ApiException $e) {
            echo "Error: Create `$username` user failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        echo "Creating the `$username` user...\n";
        $sql = "CREATE USER IF NOT EXISTS '$username'@'%' IDENTIFIED BY '$password'";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Create user `$username` failed, please check your logs.\n";
            exit;
        }
        echo "Successfully created the `$username` user!\n";

        echo "Granting `$database` database on `%` privileges to `$username`...\n";
        $sql = "GRANT ALL PRIVILEGES ON $database.* TO '$username'@'%'";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Grant privileges failed, please check your logs.\n";
            exit;
        }
        echo "Successfully granted privileges for `$username` user!\n";

        $sql = 'FLUSH PRIVILEGES';
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Flush privileges failed, please check your logs.\n";
            exit;
        }

        echo "User `$username` created successfully!\n";
    }

    /**
     * Drop a suser with all their privileges.
     *
     * @param string|null $username
     *   Username to drop.
     */
    public function dropUser(string $username = null)
    {
        try {
            $username = $username === null ? $this->config->__get(['db', 'username']) : $username;
        } catch (ApiException $e) {
            echo "Error: Drop user `$username` failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        echo "Dropping the `$username` user...\n";
        $sql = "DROP USER IF EXISTS '$username'";
        if (!$this->db->execute($sql)) {
            echo "$sql\n";
            echo "Error: Drop user `$username` failed, please check your logs.\n";
            exit;
        }
        echo "Successfully dropped the `$username` user!\n";
    }

    /**
     * Create the tables and populate them with initial Core data.
     *
     * @param string|null $basePath
     *   Base path to the Codebase.
     * @param string|null $definitionPath
     *   Path to the resource definitions, relative to basePath.
     * @param bool|null $includeTest
     *   Create the Test account, application and user.
     */
    public function createTables(string $basePath = null, string $definitionPath = null, bool $includeTest = null)
    {
        echo "Creating and populating the tables with Core data...\n";

        try {
            $basePath = $basePath === null ? $this->config->__get(['api', 'base_path']) : $basePath;
            $definitionPath = $definitionPath === null
                ? $this->config->__get(['db', 'definition_path'])
                : $definitionPath;
        } catch (ApiException $e) {
            echo "Error: Create tables failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        while (!is_bool($includeTest)) {
            $prompt = 'Include test users and accounts [y/N]: ';
            $includeTest = strtolower($this->readlineTerminal($prompt));
            $includeTest = $includeTest === 'y' ? true : $includeTest;
            $includeTest = $includeTest === 'n' || $includeTest === '' ? false : $includeTest;
        }
        $path = $basePath . $definitionPath;
        $yaml = file_get_contents($path);
        $definition = \Spyc::YAMLLoadString($yaml);

        // Parse the DB  table definition array.
        foreach ($definition as $table => $tableData) {
            $sqlPrimary = '';
            $sqlColumns = [];
            foreach ($tableData['columns'] as $column => $columnData) {
                // Column definitions.
                $sqlColumn = "`$column` ";
                if (!isset($columnData['type'])) {
                    echo "CREATE TABLE `$table` failed!\n";
                    echo "Error: Type missing in the metadata for table `$table`, please check $definitionPath.\n";
                    exit;
                }
                $sqlColumn .= ' ' . $columnData['type'];
                $sqlColumn .= isset($columnData['notnull']) && $columnData['notnull'] ? ' NOT null' : '';
                $sqlColumn .= isset($columnData['default']) ? (' DEFAULT ' . $columnData['default']) : '';
                $sqlColumn .= isset($columnData['autoincrement']) ? ' AUTO_INCREMENT' : '';
                $sqlColumn .= isset($columnData['primary']) ? ' PRIMARY KEY' : '';
                $sqlColumn .= isset($columnData['comment']) ? (" COMMENT '" . $columnData['comment'] . "'") : '';
                $sqlColumns[] = $sqlColumn;
            }
            $sqlDrop = "DROP TABLE IF EXISTS `$table`";
            if (!$this->db->execute($sqlDrop)) {
                echo "Error: Failed to drop table `$table`, please check the logs.\n";
            }
            $sqlCreate = "CREATE TABLE IF NOT EXISTS `$table` (" . implode(', ', $sqlColumns) . ');';
            if (!($this->db->execute($sqlCreate))) {
                // Stop if table create fails.
                echo "$sqlCreate\n";
                echo "Error: Failed to create the table `$table`, please check the logs.\n";
                exit;
            }

            // Add data if required.
            if (isset($tableData['data'])) {
                foreach ($tableData['data'] as $row) {
                    if ($table == 'application' && $row['name'] == 'testing' && !$includeTest) {
                        // Do not create the testing application.
                        continue;
                    }
                    if (($table == 'user' || $table == 'user_role') && !$includeTest) {
                        // Do not create the tester user and associated roles.
                        continue;
                    }
                    $keys = [];
                    $values = [];
                    foreach ($row as $key => $value) {
                        $keys[] = "`$key`";
                        $values[] = is_string($value) ? "\"$value\"" : $value;
                    }
                    $sqlRow = "INSERT INTO `$table` (" . implode(', ', $keys) . ')';
                    $sqlRow .= 'VALUES (' . implode(', ', $values) . ');';
                    if (!($this->db->execute($sqlRow))) {
                        echo "$sqlRow\n";
                        echo "Error: failed to insert a row into `$table`, please check the logs.\n";
                        exit;
                    }
                }
            }
        }

        echo "Tables successfully initialised!\n";
    }

    /**
     * Add the Core resources to the DB.
     *
     * @param string|null $basePath
     *   Base path to the Codebase.
     * @param string|null $dirResources
     *   Path to the resources directory, relative to basePath.
     */
    public function createResources(string $basePath = null, string $dirResources = null)
    {
        echo "Adding core resources to the database...\n";

        try {
            $basePath = $basePath === null ? $this->config->__get(['api', 'base_path']) : $basePath;
            $dirResources = $dirResources === null ? $this->config->__get(['api', 'dir_resources']) : $dirResources;
        } catch (ApiException $e) {
            echo "Error: Create resources failed, please check your settings.yml file.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        $dir = $basePath . $dirResources;
        $filenames = scandir($dir);

        foreach ($filenames as $filename) {
            if (pathinfo($filename, PATHINFO_EXTENSION) != 'yaml') {
                continue;
            }
            $yaml = \Spyc::YAMLLoadString(file_get_contents("$dir/$filename"));
            $name = $yaml['name'];
            $description = $yaml['description'];
            $uri = $yaml['uri'];
            $method = $yaml['method'];
            $appid = $yaml['appid'];
            $ttl = $yaml['ttl'];
            $meta = [];
            if (!empty($yaml['security'])) {
                $meta[] = '"security": ' . json_encode($yaml['security']);
            }
            if (!empty($yaml['process'])) {
                $meta[] = '"process": ' . json_encode($yaml['process']);
            }
            $meta = '{' . implode(', ', $meta) . '}';
            $sqlRow = 'INSERT INTO resource (`appid`, `name`, `description`, `method`, `uri`, `meta`, `ttl`)';
            $sqlRow .= "VALUES ($appid, '$name', '$description', '$method', '$uri', '$meta', $ttl)";
            if (!($this->db->execute($sqlRow))) {
                echo "$sqlRow\n";
                echo "Error: insert resource `$name` failed, please check your logs.\n";
                exit;
            }
        }

        echo "Resources successfully added to the DB!\n";
    }

    /**
     * Create administrator user.
     *
     * @param string $username
     *   Admin user username.
     * @param string $password
     *   Admin user password.
     * @param string $email
     *   Admin user email.
     */
    public function createAdminUser(string $username = '', string $password = '', string $email = '')
    {
        echo "Creating the admin user...\n";

        while ($username == '') {
            $prompt = 'Enter the admin users username: ';
            $username = $this->readlineTerminal($prompt);
        }
        while ($password == '') {
            $prompt = 'Enter the admin users password: ';
            $password = $this->readlineTerminal($prompt);
        }
        while ($email == '') {
            $prompt = 'Enter the admin users email: ';
            $email = $this->readlineTerminal($prompt);
        }

        try {
            $userMapper = new Db\UserMapper($this->db);
            $user = new Db\User(
                null,
                1,
                $username,
                null,
                null,
                null,
                $email,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            );
            $user->setPassword($password);
            $userMapper->save($user);
        } catch (ApiException $e) {
            echo "Error: an error occurred creating your user, please check the logs.\n";
            echo $e->getMessage() . "\n";
            exit;
        }
        echo "User $username created!\n";

        // Assign administrator role.
        echo "Assigning the administrator role to your use...\n";
        try {
            $user = $userMapper->findByUsername($username);
            $uid = $user->getUid();
            if (empty($uid)) {
                echo "Error: Could not find the newly created user, please check the logs.\n";
                exit;
            }
            $roleMapper = new Db\RoleMapper($this->db);
            $role = $roleMapper->findByName('Administrator');
            if (empty($uid)) {
                echo "Error: Could not find the administrator role, please check the logs.\n";
                exit;
            }
            $rid = $role->getRid();
            $userRole = new Db\UserRole(
                null,
                null,
                null,
                $uid,
                $rid
            );
            $userRoleMapper = new Db\UserRoleMapper($this->db);
            $userRoleMapper->save($userRole);
        } catch (ApiException $e) {
            echo "Error: An error occurred creating your Administrator role, please check the logs.\n";
            echo $e->getMessage() . "\n";
            exit;
        }

        echo "Administrator role successfully added to $username!\n\n";
    }
}