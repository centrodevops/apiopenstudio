<?php

/**
 * Parent class for mixed variable types
 */

namespace Gaterdata\Processor;
use Gaterdata\Core;

class VarMixed extends Core\ProcessorEntity
{
  protected $details = array(
    'name' => 'Var (Mixed)',
    'machineName' => 'var_mixed',
    'description' => 'A variable of any type.',
    'menu' => 'Primitive',
    'application' => 'Common',
    'input' => array(
      'value' => array(
        'description' => 'The value of the variable.',
        'cardinality' => array(1, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array(),
        'limitValues' => array(),
        'default' => ''
      ),
    ),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

    $result = $this->val('value');
    if (!$this->isDataContainer($result)) {
      $result = new Core\DataContainer($result, 'text');
    }

    return $result;
  }
}