<?php

/**
 * Account delete.
 */

namespace Gaterdata\Processor;
use Gaterdata\Core;
use Gaterdata\Db;

class AccountDelete extends Core\ProcessorEntity
{
  /**
   * @var array
   *  The processor details.
   */
  protected $details = [
    'name' => 'Account delete',
    'machineName' => 'account_delete',
    'description' => 'Delete an account.',
    'menu' => 'Admin',
    'input' => [
      'accid' => [
        'description' => 'The account ID.',
        'cardinality' => [1, 1],
        'literalAllowed' => TRUE,
        'limitFunctions' => [],
        'limitTypes' => ['integer'],
        'limitValues' => [],
        'default' => ''
      ],
    ],
  ];

  /**
   * @return array|bool|Core\Error
   * @throws Core\ApiException
   */
  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

    $accid = $this->val('accid', TRUE);
    Core\Debug::variable($accid);

    $accountMapper = new Db\AccountMapper($this->db);
    $account = $accountMapper->findByAccid($accid);

    if (empty($account->getAccid())) {
      throw new Core\ApiException("Account does not exist: $accid",6, $this->id, 400);
    }
    // Do not delete if applications are attached to the account.
    $applicationMapper = new Db\ApplicationMapper($this->db);
    $applications = $applicationMapper->findByAccid($accid);
    if (!empty($applications)) {
      throw new Core\ApiException('Cannot delete the account, applications are assigned to the account',6, $this->id, 400);
    }

    return $accountMapper->delete($account);
  }
}