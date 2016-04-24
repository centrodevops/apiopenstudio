<?php

/**
 * Provide token authentication based on token
 *
 * Meta:
 *    {
 *      "type": "token",
 *      "meta": {
 *        "id":<integer>,
 *        "token": <processor|string>
 *      }
 *    }
 */

namespace Datagator\Security;
use Datagator\Core;

class TokenUser extends Token {
  protected $role = false;
  public $details = array(
    'machineName' => 'tokenUser',
    'name' => 'Token (User)',
    'description' => 'Validate the request by user and token, only allowing specific users to use the resource.',
    'menu' => 'Security',
    'client' => 'All',
    'application' => 'All',
    'inputs' => array(
      'token' => array(
        'description' => 'The consumers token.',
        'cardinality' => array(1),
        'accepts' => array('processor')
      ),
      'usernames' => array(
        'description' => "The username/s.",
        'cardinality' => array('?'),
        'accepts' => array('processor', 'literal', 'array'),
      ),
    ),
  );

  /**
   * @return bool
   * @throws \Datagator\Core\ApiException
   */
  public function process() {
    Core\Debug::variable($this->meta, 'Validator TokenConsumer', 4);

    // check user exists
    $this->request->user->findByToken($this->val($this->meta->token));
    if (!$this->request->user->exists() || !$this->request->user->isActive()) {
      throw new Core\ApiException('permission denied', 4, $this->id, 401);
    }
    // check user is in the list of valid users
    $usernames = $this->val($this->meta->usernames);
    if (!is_array($usernames)) {
      $usernames = array($usernames);
    }
    $user = $this->request->user->getUser();
    foreach ($usernames as $username) {
      if ($username != $user->getUsername()) {
        throw new Core\ApiException('permission denied', 4, $this->id, 401);
      }
    }

    return TRUE;
  }
}
