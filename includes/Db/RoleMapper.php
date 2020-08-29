<?php
/**
 * Class RoleMapper.
 *
 * @package Gaterdata\Db
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0-or-later
 * @author john89
 * @copyright 2020-2030 GaterData
 * @link https://gaterdata.com
 */

namespace Gaterdata\Db;

/**
 * Class RoleMapper.
 *
 * Mapper class for DB calls used for the role table.
 */
class RoleMapper extends Mapper
{
    /**
     * Save a Role object into the DB.
     *
     * @param \Gaterdata\Db\Role $role Role object.
     *
     * @return boolean Success.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     */
    public function save(Role $role)
    {
        if ($role->getRid() == null) {
            $sql = 'INSERT INTO role (name) VALUES (?)';
            $bindParams = [
            $role->getName(),
            ];
        } else {
            $sql = 'UPDATE role SET name = ? WHERE rid = ?';
            $bindParams = [
            $role->getName(),
            $role->getRid(),
            ];
        }
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Delete a Role.
     *
     * @param \Gaterdata\Db\Role $role Role object.
     *
     * @return boolean Success.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     */
    public function delete(Role $role)
    {
        $sql = 'DELETE FROM role WHERE rid = ?';
        $bindParams = [$role->getRid()];
        return $this->saveDelete($sql, $bindParams);
    }

    /**
     * Find all roles.
     *
     * @param array $params Filter parameters.
     *
     * @return array Array of role objects.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     */
    public function findAll(array $params = [])
    {
        $sql = 'SELECT * FROM role';
        return $this->fetchRows($sql, [], $params);
    }

    /**
     * Find a role by its ID.
     *
     * @param integer $rid Role ID.
     *
     * @return \Gaterdata\Db\Role Role object.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     *
     */
    public function findByRid(int $rid)
    {
        $sql = 'SELECT * FROM role WHERE rid = ?';
        $bindParams = [$rid];
        return $this->fetchRow($sql, $bindParams);
    }

    /**
     * Find a role by its name.
     *
     * @param string $name Role name.
     *
     * @return \Gaterdata\Db\Role Role object.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     */
    public function findByName(string $name)
    {
        $sql = 'SELECT * FROM role WHERE name = ?';
        $bindParams = [$name];
        return $this->fetchRow($sql, $bindParams);
    }

    /**
     * Find a roles a user has assigned.
     *
     * @param integer $uid User ID.
     *
     * @return array Array of role objects.
     *
     * @throws \Gaterdata\Core\ApiException Return an ApiException on DB error.
     */
    public function findByUid(int $uid)
    {
        $sql = 'SELECT DISTINCT r.* FROM role r INNER JOIN user_role ur ON ur.rid = r.rid WHERE ur.uid = ?';
        $bindParams = [$uid];
        return $this->fetchRows($sql, $bindParams);
    }

    /**
     * Map a DB row to the internal attributes.
     *
     * @param array $row DB row.
     *
     * @return \Gaterdata\Db\Role
     *   Role object.
     */
    protected function mapArray(array $row)
    {
        $role = new Role();

        $role->setRid(!empty($row['rid']) ? $row['rid'] : null);
        $role->setName(!empty($row['name']) ? $row['name'] : null);

        return $role;
    }
}
