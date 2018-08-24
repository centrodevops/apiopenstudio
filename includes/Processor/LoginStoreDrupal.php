<?php

/**
 * Store the Drupal login token
 *
 * Takes the input of a login event and stores the token if successful
 *
 *
 * METADATA
 * {
 *  "type":"tokenStoreDrupal",
 *  "meta": {
 *    "id":<integer>,
 *    "source":<processor>
 *  }
 * }
 */

namespace Datagator\Processor;
use Datagator\Core;
use Datagator\Db;

class LoginStoreDrupal extends Core\ProcessorEntity
{
  private $user;
  private $defaultEntity = 'drupal';
  protected $details = array(
    'name' => 'Login Store Drupal',
    'machineName' => 'loginStoreDrupal',
    'description' => 'Login the user Stores the access details from a users login to a remote drupal site for future use.',
    'menu' => 'External',
    'application' => 'Common',
    'input' => array(
      'source' => array(
        'description' => 'The results of a login attempt to the remote site. i.e. Processor InputUrl.',
        'cardinality' => array(1, 1),
        'literalAllowed' => false,
        'limitFunctions' => array(),
        'limitTypes' => array('string'),
        'limitValues' => array(),
        'default' => ''
      ),
      'externalEntity' => array(
        'description' => 'The name of the external entity this user is tied to (use custom names if you access more than one drupal site).',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('string'),
        'limitValues' => array(),
        'default' => 'drupal'
      ),
    ),
  );

  /**
   * @param $meta
   * @param $request
   */
  public function __construct($meta, $request)
  {
    parent::__construct($meta, $request);
    $db = $this->getDb();
    $this->user = new Db\ExternalUserMapper($db);
  }

  /**
   * @return array|mixed
   * @throws \Datagator\Core\ApiException
   * @throws \Datagator\Processor\ApiException
   */
  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor LoginStoreDrupal', 4);

    $source = $this->val('source');
    $source = json_decode($source);
    if (empty($source->token) || empty($source->user) || empty($source->user->uid)) {
      throw new Core\ApiException('login failed, no token received', 4, $this->id, 419);
    }
    $externalEntity = !empty($this->meta->externalEntity) ? $this->val('externalEntity') : $this->defaultEntity;
    $externalId = $source->user->uid;
    $appid = $this->request->appId;
    $db = $this->getDb();

    $userMapper = new Db\ExternalUserMapper($db);
    $user = $userMapper->findByAppIdEntityExternalId($appid, $externalEntity, $externalId);
    if ($user->getId() == NULL) {
      $user->setAppId($appid);
      $user->setExternalEntity($externalEntity);
      $user->setExternalId($externalId);
    }
    $user->setDataField1($source->token);
    $user->setDataField2($source->session_name);
    $user->setDataField3($source->sessid);

    $userMapper->save($user);

    return $source;
  }
}