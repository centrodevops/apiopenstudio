<?php

/**
 * Resource import and export.
 * Allowed inputs are yaml files or yaml strings.
 *
 * METADATA
 * {
 *    "type":"object",
 *    "meta":{
 *      "id": <mixed>,
 *      "yaml": <string>,
 *      "method": <"get"|"post">,
 *      "resource": <mixed>,
 *      "action": <mixed>
 *    }
 *  }
 */

namespace Datagator\Processor;
use Datagator\Core;

class ResourceYaml extends ResourceBase
{
  public $details = array(
    'name' => 'Resource (Yaml)',
    'description' => 'Create or fetch a custom API resource for the application in YAML form.',
    'menu' => 'Resource',
    'application' => 'All',
    'input' => array(
      'method' => array(
        'description' => 'The HTTP method of the resource (only used if fetching or deleting a resource).',
        'cardinality' => array(0, 1),
        'accepts' => array('processor', '"get"', '"post"', '"delete"', '"push"'),
      ),
      'appid' => array(
        'description' => 'The application ID the resource is associated with (only used if fetching or deleting a resource).',
        'cardinality' => array(0, 1),
        'accepts' => array('integer')
      ),
      'noun' => array(
        'description' => 'The noun identifier of the resource (only used if fetching or deleting a resource).',
        'cardinality' => array(0, 1),
        'accepts' => array('literal')
      ),
      'verb' => array(
        'description' => 'The verb identifier of the resource (only used if fetching or deleting a resource).',
        'cardinality' => array(0, 1),
        'accepts' => array('literal')
      ),
      'yaml' => array(
        'description' => 'The yaml string or file. This can be a form file or a urlencoded GET var (this input is only used if you are creating or updating a resource).',
        'cardinality' => array(0, 1),
        'accepts' => array('string', 'file')
      )
    )
  );

  /**
   * @return array|string
   * @throws \Datagator\Core\ApiException
   */
  protected function _importData()
  {
    // extract yaml
    $yaml = '';
    if (sizeof($_FILES) > 1) {
      throw new Core\ApiException('multiple files received', 3);
    }
    if (!empty($_FILES)) {
      foreach ($_FILES as $file) {
        $yaml = \Spyc::YAMLLoad($file['tmp_name']);
      }
    } else {
      if (empty($this->request->vars['yaml'])) {
        throw new Core\ApiException('no yaml supplied', 6, $this->id, 417);
      }
      $yaml = $this->val($this->meta->yaml);
      $yaml = urldecode($yaml);
      $yaml = \Spyc::YAMLLoadString($yaml);
    }
    return $yaml;
  }

  /**
   * @param array $array
   * @return string
   */
  protected function _exportData(array $array)
  {
    return \Spyc::YAMLDump($array);
  }
}
