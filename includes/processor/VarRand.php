<?php

/**
 * Variable type random.
 */

namespace Datagator\Processor;
use Datagator\Core;

class VarRand extends ProcessorEntity
{
  protected $details = array(
    'name' => 'Var (Rand)',
    'description' => 'A random variable. It produces a random variable of any specified length or mix of character types.',
    'menu' => 'Primitive',
    'application' => 'Common',
    'input' => array(
      'length' => array(
        'description' => 'The length of the variable.',
        'cardinality' => array(1, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('integer'),
        'limitValues' => array(),
        'default' => 8
      ),
      'lower' => array(
        'description' => 'Use lower-case alpha characters.',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('boolean'),
        'limitValues' => array(),
        'default' => true
      ),
      'upper' => array(
        'description' => 'Use upper-case alpha characters.',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('boolean'),
        'limitValues' => array(),
        'default' => true
      ),
      'numeric' => array(
        'description' => 'Use numeric characters.',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('boolean'),
        'limitValues' => array(),
        'default' => true
      ),
      'special' => array(
        'description' => 'Use special characters.',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('boolean'),
        'limitValues' => array(),
        'default' => true
      ),
    ),
  );


  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor VarRand', 4);

    $length = $this->val($this->meta->length);
    $lower = $this->val($this->meta->lower);
    $upper = $this->val($this->meta->upper);
    $numeric = $this->val($this->meta->numeric);
    $special = $this->val($this->meta->special);

    return Core\Utilities::random_string($length, $lower, $upper, $numeric, $special);
  }
}
