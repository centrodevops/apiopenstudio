<?php

/**
 * Perform string concatenation of two or more inputs
 */

namespace Gaterdata\Processor;
use Gaterdata\Core;

class Concatenate extends Core\ProcessorEntity
{
  protected $details = array(
    'name' => 'Concatenate',
    'machineName' => 'concatenate',
    'description' => 'Concatenate a series of strings or numbers into a single string.',
    'menu' => 'Operation',
    'application' => 'Common',
    'input' => array(
      'sources' => array(
        'description' => 'The values to concatenate',
        'cardinality' => array(2, '*'),
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

    $sources = $this->val('sources');
    $result = '';
    foreach ($sources as $source) {
      $result .= (string) $this->isDataContainer($source) ? $source->getData() : $source;
    }

    return new Core\DataContainer($result, 'text');
  }
}
