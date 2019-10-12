<?php

/**
 * Update an applications.
 */

namespace Gaterdata\Processor;
use Gaterdata\Core;
use Gaterdata\Core\ApiException;
use Gaterdata\Db;

class ApplicationUpdate extends Core\ProcessorEntity
{
  protected $details = [
    'name' => 'Application update',
    'machineName' => 'application_update',
    'description' => 'Update an application.',
    'menu' => 'Admin',
    'input' => [
      'appid' => [
        'description' => 'The application iD.',
        'cardinality' => [1, 1],
        'literalAllowed' => TRUE,
        'limitFunctions' => [],
        'limitTypes' => ['integer'],
        'limitValues' => [],
        'default' => ''
      ],
      'accid' => [
        'description' => 'The parent account ID for the application.',
        'cardinality' => [1, 1],
        'literalAllowed' => TRUE,
        'limitFunctions' => [],
        'limitTypes' => ['integer'],
        'limitValues' => [],
        'default' => ''
      ],
      'name' => [
        'description' => 'The application name.',
        'cardinality' => [1, 1],
        'literalAllowed' => TRUE,
        'limitFunctions' => [],
        'limitTypes' => ['string'],
        'limitValues' => [],
        'default' => ''
      ],
    ],
  ];

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

    $appid = $this->val('appid', TRUE);
    $accid = $this->val('accid', TRUE);
    $name = $this->val('name', TRUE);

    $accountMapper = new Db\AccountMapper($this->db);
    $applicationMapper = new Db\ApplicationMapper($this->db);

    $application = $applicationMapper->findByAppid($appid);
    if (empty($application->getAccid())) {
      throw new ApiException("Application ID does not exist: $appid", 6, $this->id, 417);
    }
    $account = $accountMapper->findByAccid($accid);
    if (empty($account->getAccid())) {
      throw new ApiException("Account ID does not exist: $accid", 6, $this->id, 417);
    }
    
    $application->setAccid($accid);
    $application->setName($name);

    return $applicationMapper->save($application);
  }
}
