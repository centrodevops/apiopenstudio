<?php

/**
 * Class Json.
 *
 * @package    ApiOpenStudio\Output
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Output;

use ApiOpenStudio\Core\ApiException;
use ApiOpenStudio\Core\Config;
use ApiOpenStudio\Core\ConvertToJsonTrait;
use ApiOpenStudio\Core\DetectTypeTrait;
use ApiOpenStudio\Core\MonologWrapper;
use ApiOpenStudio\Core\OutputResponse;

/**
 * Class Json
 *
 * Outputs the results as a JSON string.
 */
class Json extends OutputResponse
{
    use ConvertToJsonTrait;
    use DetectTypeTrait;

    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected array $details = [
        'name' => 'Json',
        'machineName' => 'json',
        // phpcs:ignore
        'description' => 'Output the results of the resource in JSON format in the response. This does not need to be added to the resource - it will be automatically detected by the Accept header.',
        'menu' => 'Output',
        'input' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @var string The string to contain the content type header value.
     */
    protected string $header = 'Content-Type: application/json';

    /**
     * Config object.
     *
     * @var Config
     */
    protected Config $settings;

    public function __construct($data, int $status, MonologWrapper $logger, $meta = null)
    {
        parent::__construct($data, $status, $logger, $meta);
        $this->settings = new Config();
    }

    /**
     * Cast the data to JSON.
     *
     * @throws ApiException
     *   Throw an exception if unable to convert the data.
     */
    protected function castData(): void
    {
        $currentType = $this->data->getType();
        if ($currentType == 'json') {
            return;
        }

        $inputData = $this->data->getData();
        $inputType = $this->data->getType();

        $method = 'from' . ucfirst(strtolower($currentType)) . 'ToJson';
        $resultData = $this->$method($inputData);

        if ($this->settings->__get(['api', 'wrap_json_in_response_object'])) {
            // Wrap JSON in the wrapper object if required by the settings.
            if (in_array($inputType, ['json', 'array', 'xml', 'html']) && !is_bool($resultData)) {
                $decoded = json_decode($resultData, true);
                $resultData = is_null($decoded) ? $resultData : $decoded;
            }
            if (
                !is_array($resultData)
                || sizeof($resultData) != 2
                || !isset($resultData['result'])
                || !isset($resultData['data'])
            ) {
                $resultData = [
                    'result' => 'ok',
                    'data' => $resultData,
                ];
            }
            $resultData = json_encode($resultData);
        } elseif ($inputType == 'text') {
            // Wrap text values in double quotes so that they are parseable as valid JSON.
            $resultData = '"' . $resultData . '"';
        }


        try {
            $this->data->setData($resultData);
            $this->data->setType('json');
        } catch (ApiException $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $this->id, $e->getHtmlCode());
        }
    }
}
