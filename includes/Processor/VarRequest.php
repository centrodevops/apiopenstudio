<?php

/**
 * Class VarRequest.
 *
 * @package    ApiOpenStudio
 * @subpackage Processor
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Processor;

use ApiOpenStudio\Core;

/**
 * Class VarRequest
 *
 * Processor class to return the post and get variables in a request.
 */
class VarRequest extends Core\ProcessorEntity
{
    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected $details = [
        'name' => 'Var (Request)',
        'machineName' => 'var_request',
        'description' => 'A "get" or "post" variable. It fetches a variable from the get or post requests.',
        'menu' => 'Primitive',
        'input' => [
            'key' => [
                'description' => 'The key or name of the GET/POST variable.',
                'cardinality' => [1, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
            'nullable' => [
                'description' => 'Allow the processing to continue if the GET or POST variable does not exist.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['boolean', 'integer'],
                'limitValues' => [],
                'default' => true,
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @return Core\DataContainer Result of the processor.
     *
     * @throws Core\ApiException Exception if invalid result.
     */
    public function process()
    {
        $this->logger->info('Processor: ' . $this->details()['machineName']);

        $key = $this->val('key', true);
        $vars = array_merge($this->request->getGetVars(), $this->request->getPostVars());

        if (isset($vars[$key])) {
            return new Core\DataContainer($vars[$key], 'text');
        }
        if (filter_var($this->val('nullable', true), FILTER_VALIDATE_BOOLEAN)) {
            return new Core\DataContainer('', 'text');
        }
        throw new Core\ApiException("request var $key not available", 6, $this->id, 400);
    }
}
