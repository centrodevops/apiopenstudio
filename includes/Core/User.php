<?php

/**
 *
 */

namespace Datagator\Core;
use Datagator\Db;

class User
{
  protected $db;
  protected $user;

  /**
   * @param $dbLayer
   */
  public function __construct($dbLayer)
  {
    $this->db = $dbLayer;
  }

  /**
   * @param \Datagator\Db\User $user
   */
  public function setUser(Db\User $user)
  {
    $this->user = $user;
  }

  /**
   * @return \Datagator\Db\User|NULL
   */
  public function getUser()
  {
    return $this->user;
  }

  public function save()
  {
    $mapper = new Db\UserMapper($this->db);
    $mapper->save($this->user);
  }

  /**
   * @param $token
   * @return \Datagator\Db\User
   */
  public function findByToken($token)
  {
    $mapper = new Db\UserMapper($this->db);
    $this->user = $mapper->findBytoken($token);
    return $this->user;
  }

  /**
   * @param $username
   * @return \Datagator\Db\User
   */
  public function findByUsername($username)
  {
    $mapper = new Db\UserMapper($this->db);
    $this->user = $mapper->findByUsername($username);
    return $this->user;
  }

  /**
   * Check to see if the user has a specific role with the application
   * $app can be application name or appid
   * $role can be role name or rid
   *
   * @param $appId
   * @param $rid
   * @return bool
   */
  public function hasRole($appId, $rid)
  {
    // not a valid user
    if (empty($uid = $this->user->getUid())) {
      return false;
    }
    // convert app name to app Id
    if (!is_int($appId)) {
      $appMapper = new Db\ApplicationMapper($this->db);
      $app = $appMapper->findByName($appId);
      $appId = $app->getAppId();
    }
    // convert role name to role id
    if (!is_int($rid)) {
      $roleMapper = new Db\RoleMapper($this->db);
      $row = $roleMapper->findByName($rid);
      $role = $row->getRid();
    }
    $userMapper = new Db\UserMapper($this->db);
    return $userMapper->hasRole($uid, $appId, $role);
  }
}
