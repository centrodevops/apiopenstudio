<?php

/**
 * Simple field type.
 * Inputs:
 *   * array => returns [key => value]
 *   * key => [key => '']
 *   * value => [0 => value]
 *   * key & value => [key => value]
 */

namespace Gaterdata\Processor;

use Gaterdata\Core;

class VarField extends Core\ProcessorEntity
{
    /**
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Var (field)',
        'machineName' => 'var_field',
        'description' => 'Create a name value pair. This is primarily for use as a field in object. individual key/values can be input or a whole array. ',
        'menu' => 'Primitive',
        'input' => [
            'key' => [
                'description' => 'The key of the field name/value pair.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['text', 'integer'],
                'limitValues' => [],
                'default' => 0,
            ],
            'value' => [
                'description' => 'The value of the field name/value pair.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => [],
                'limitValues' => [],
                'default' => '',
            ],
            'array' => [
                'description' => 'Array to be converted to a field. This can only have one index.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['array'],
                'limitValues' => [],
                'default' => [],
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function process()
    {
        $this->logger->info('Processor: ' . $this->details()['machineName']);

        $array = $this->val('array', true);
        $key = $this->val('key', true);
        $value = $this->val('value', true);

        if (!empty($array)) {
            if (sizeof($array) > 1) {
                throw new Core\ApiException('Cannot have more than one index in an input array.', 0, $this->id, 417);
            }
            $keys = array_keys($array);
            return new Core\DataContainer([$keys[0] => $array[$keys[0]]], 'array');
        }

        return new Core\DataContainer([$key => $value], 'array');
    }
}
