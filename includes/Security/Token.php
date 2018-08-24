<?php

/**
 * Provide token authentication based on token.
 */

namespace Datagator\Security;
use Datagator\Core;
use Datagator\Db;

class Token extends Core\ProcessorEntity
{
  protected $role = false;
  protected $details = array(
    'name' => 'Token',
    'machineName' => 'token',
    'description' => 'Validate the request, requiring the consumer to have a valid token.',
    'menu' => 'Security',
    'application' => 'Common',
    'input' => array(
      'token' => array(
        'description' => 'The consumers token.',
        'cardinality' => array(1, 1),
        'literalAllowed' => false,
        'limitFunctions' => array(),
        'limitTypes' => array('string'),
        'limitValues' => array(),
        'default' => ''
      )
    ),
  );

  /**
   * @return array
   * @throws \Datagator\Core\ApiException
   */
  public function process() {
    Core\Debug::variable($this->meta, 'Security Token', 4);
    $token = $this->val('token', true);

    // no token
    if (empty($token)) {
      throw new Core\ApiException('permission denied', 4, -1, 401);
    }

    // invalid token or user not active
    $db = $this->getDb();
    $userMapper = new Db\UserMapper($db);
    $user = $userMapper->findBytoken($token);
    if (empty($user->getUid()) || $user->getActive() == 0) {
      throw new Core\ApiException('permission denied', 4, -1, 401);
    }

    // get role from DB
    $roleMapper = new Db\RoleMapper($db);
    $this->role = $roleMapper->findByName($this->role);

    // return list of roles for user for this request app
    $userRoleMapper = new Db\UserRoleMapper($db);
    return $userRoleMapper->findByMixed($user->getUid(), $this->request->getAppId());
  }
}