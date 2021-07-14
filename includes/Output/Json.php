<?php

/**
 * Class Json.
 *
 * @package    ApiOpenStudio
 * @subpackage Output
 * @author     john89 (https://gitlab.com/john89)
 * @copyright  2020-2030 Naala Pty Ltd
 * @license    This Source Code Form is subject to the terms of the ApiOpenStudio Public License.
 *             If a copy of the license was not distributed with this file,
 *             You can obtain one at https://www.apiopenstudio.com/license/.
 * @link       https://www.apiopenstudio.com
 */

namespace ApiOpenStudio\Output;

use ApiOpenStudio\Core\DataContainer;

/**
 * Class Json
 *
 * Outputs the results as a JSON.
 */
class Json extends Output
{
    /**
     * {@inheritDoc}
     *
     * @var string The string to contain the content type header value.
     */
    protected string $header = 'Content-Type: application/json';

    /**
     * {@inheritDoc}
     *
     * @var array Details of the processor.
     */
    protected array $details = [
        'name' => 'Json',
        'machineName' => 'json',
        'description' => 'Output in the results of the resource in JSON format to a remote server.',
        'menu' => 'Output',
        'input' => [
            'destination' => [
                'description' => 'Destination URLs for the output.',
                'cardinality' => [0, '*'],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
            'method' => [
                'description' => 'HTTP delivery method when sending output. Only used in the output section.',
                'cardinality' => [0, 1],
                'literalAllowed' => true,
                'limitProcessors' => [],
                'limitTypes' => ['text'],
                'limitValues' => ['get', 'post', 'push', 'delete', 'put'],
                'default' => '',
            ],
            'options' => [
                // phpcs:ignore
                'description' => 'Extra Curl options to be applied when sent to the destination (e.g. cursor: -1, screen_name: foobarapi, skip_status: true, etc).',
                'cardinality' => [0, '*'],
                'literalAllowed' => true,
                'limitProcessors' => ['field'],
                'limitTypes' => ['text'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @return DataContainer Result of the processor.
     */
    public function process(): DataContainer
    {
        $this->logger->info('Output: ' . $this->details()['machineName']);
        return new DataContainer(parent::process(), 'text');
    }

    /**
     * {@inheritDoc}
     *
     * @param boolean $data Boolean data.
     *
     * @return string JSON string.
     */
    protected function fromBoolean(bool &$data): string
    {
        return $data ? 'true' : 'false';
    }

    /**
     * {@inheritDoc}
     *
     * @param int $data Integer data.
     *
     * @return int JSON string.
     */
    protected function fromInteger(int &$data): int
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @param float $data Float data.
     *
     * @return float JSON string.
     */
    protected function fromFloat(float &$data): float
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data XML data.
     *
     * @return string JSON string.
     */
    protected function fromXml(string &$data)
    {
        $xml = simplexml_load_string($data);
        return $this->xml2json($xml);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data HTML data.
     *
     * @return string JSON string.
     */
    protected function fromHtml(string &$data)
    {
        return $this->fromXml($data);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data Text data.
     *
     * @return string JSON string.
     */
    protected function fromText(string &$data): string
    {
        if ($data == '') {
            // Empty string should be returned as double quotes so that it is not returned as null.
            return '""';
        }
        // Wrap in double quotes if not already present.
        if (substr($data, 0, 1) != '"' && substr($data, 0, 6) != '&quot;') {
            $data = '"' . $data;
        }
        if (substr($data, -1, 1) != '"' && substr($data, -6, 6) != '&quot;') {
            $data = $data . '"';
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @param array $data Array data.
     *
     * @return string JSON string.
     */
    protected function fromArray(array &$data): string
    {
        return \json_encode($data);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $data Json data.
     *
     * @return string JSON string.
     */
    protected function fromJson(string &$data): string
    {
        return is_string($data) ? $data : \json_encode($data);
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed $data Image data.
     *
     * @return string JSON string.
     */
    protected function fromImage(&$data): string
    {
        return $this->fromText($data);
    }

    /**
     * Convert XML data to JSON format.
     *
     * @param \SimpleXMLElement $xml XML element.
     *
     * @return array|false|string
     */
    private function xml2json(\SimpleXMLElement &$xml)
    {
        $root = !(func_num_args() > 1);
        $jsnode = array();

        if (!$root) {
            if (count($xml->attributes()) > 0) {
                $jsnode["$"] = array();
                foreach ($xml->attributes() as $key => $value) {
                    $jsnode["$"][$key] = (string)$value;
                }
            }

            $textcontent = trim((string)$xml);
            if (count($textcontent) > 0) {
                $jsnode["_"] = $textcontent;
            }

            foreach ($xml->children() as $childxmlnode) {
                $childname = $childxmlnode->getName();
                if (!array_key_exists($childname, $jsnode)) {
                    $jsnode[$childname] = array();
                }
                array_push($jsnode[$childname], $this->xml2json($childxmlnode));
            }
            return $jsnode;
        } else {
            $nodename = $xml->getName();
            $jsnode[$nodename] = array();
            array_push($jsnode[$nodename], $this->xml2json($xml));
            return json_encode($jsnode);
        }
    }
}
