<?php

/**
 *
 */

namespace Gaterdata\Processor;
use Gaterdata\Core;
//use Peekmo\JsonPath\JsonStore;
use JmesPath;

class Mapper extends Core\ProcessorEntity
{
  protected $details = array(
    'name' => 'Mapper',
    'machineName' => 'mapper',
    'description' => 'Mapper allows the mapping of elements from a source to a destination.',
    'menu' => 'Operation',
    'application' => 'Common',
    'input' => array(
      'source' => array(
        'description' => 'The source object to perform the mapping on.',
        'cardinality' => array(1, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array(),
        'limitValues' => array(),
        'default' => ''
      ),
      'mappings' => array(
        'description' => 'A list of individual xpath values for the the get/set tuplets ( e.g. ["get": "//foo/bar", "set": "/foo/bar])".',
        'cardinality' => array(0, '*'),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array(),
        'limitValues' => array(),
        'default' => ''
      ),
      'format' => array(
        'description' => 'Output format. If ommitted, the result wil be same format as input.',
        'cardinality' => array(0, 1),
        'literalAllowed' => true,
        'limitFunctions' => array(),
        'limitTypes' => array('string'),
        'limitValues' => array('xml', 'json'),
        'default' => ''
      ),
    ),
  );

  private $result = '';

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor ' . $this->details()['machineName'], 2);

    $source = $this->val('source');
    $type = $source->getType();
    $sourceData = $source->getData();
    $mappings = $this->val('mappings', true);
    $format = strtolower($this->val('format', true));
    $format = empty($format) ? $type : $format;

    switch ($format) {
      case 'json':
        $this->result = array();
        if (is_string($sourceData)) {
          $sourceData = \json_decode($sourceData);
        }
        break;
      case 'xml':
      default:
        $this->result = new \DOMDocument();
        $this->result->formatOutput = true;
        if (is_string($sourceData)) {
          $xml = $sourceData;
          $sourceData = new \DOMDocument();
          $sourceData->loadXML($xml);
        }
        break;
    }

    switch($type) {
      case 'xml':
        $this->_mapXml($sourceData, $mappings, $format);
        break;
      case 'json':
        $this->_mapJson($sourceData, $mappings, $format);
        break;
      default:
        throw new Core\ApiException("can only perform mappings on types XML or JSON. '$type' received", 6, $this->id, 417);
        break;
    }

    switch ($format) {
      case 'json':
        return new Core\DataContainer(\json_encode($this->result), $format);
        break;
      case 'xml':
      default:
        return new Core\DataContainer($this->result->saveXML(), $format);
        break;
    }
  }

  /**
   * Perform mappings to JSON source.
   *
   * @param $source
   * @param $mappings
   * @param $format
   * @return \Gaterdata\Core\DataContainer
   * @throws \Gaterdata\Core\ApiException
   * @see https://github.com/jmespath/jmespath.php
   */
  private function _mapJson(\stdClass & $source, $mappings, $format) {
    $resultFunc = '_addResult' . ucfirst($format);

    foreach ($mappings as $index => $mapping) {
      if (empty($mapping->get) || empty($mapping->set)) {
        throw new Core\ApiException("missing get or set indices a index: $index", 6, $this->id, 417);
      }
      try {
        $value = JmesPath\search($mapping->get, $source);
      } catch(\Exception $e) {
        throw new Core\ApiException($e->getMessage(), 6, $this->id, 417);
      }
      $this->{$resultFunc}($mapping->set, $value);
    }

    return;
  }

  /**
   * Add to result object.
   *  parents separated by '/'
   *  foobar[] - add to array foobar
   *  foo[fbar] = add to object foo as index bar
   *
   * @param $regex
   * @param $value
   */
  private function _addResultJson($regex, $value)
  {
    $nodes = explode('/', $regex);
    while (empty($nodes[0])) {
      array_shift($nodes);
    }
    $currentNode = & $this->result;

    while ($node = array_shift($nodes)) {
      $node = preg_replace('/[^A-Za-z0-9\[\]]/', '', $node); // strip all non-special characters
      if (empty($node)) {
        continue;
      }

      if (!empty($nodes)) {
        // not last one, add node (if not already exists)
        if (!isset($currentNode[$node])) {
          $currentNode[$node] = array();
        }
        $currentNode = & $currentNode[$node];
      } else {
        // this is last one, add node and value
        if (preg_match("/.+\[\]/", $node)) {
          // add value as array value
          $left = strpos($node, '[');
          $nodeName = substr($node, 0, $left);
          if (!isset($currentNode[$nodeName])) {
            $currentNode[$nodeName] = array();
          }
          if (!empty($value)) {
            $currentNode[$nodeName] += array_merge($currentNode[$nodeName], $value);
          }
        } elseif (preg_match("/.+\[.+\]/", $node)) {
          // add value as object value with textual index
          $left = strpos($node, '[');
          $right = strpos($node, ']');
          $nodeName = substr($node, 0, $left);
          $nodeKey = substr($node, $left + 1, $right - $left - 1);
          $currentNode[$nodeName][$nodeKey] = !is_array($value) ? $value : (sizeof($value) < 1 ? '' : (sizeof($value) < 2 ? $value[0] : $value));
        }
        $currentNode = & $this->result;
      }
    }
  }

  /**
   * Perform mappings to XML source.
   *
   * @param $source
   * @param $mappings
   * @param $format
   * @return \Gaterdata\Core\DataContainer
   * @throws \Gaterdata\Core\ApiException
   */
  private function _mapXml(\DOMDocument $source, $mappings, $format)
  {
    $resultFunc = '_addResult' . ucfirst($format);
    $xpath = new \DOMXPath($source);

    foreach ($mappings as $index => $mapping) {
      if (empty($mapping->get) || empty($mapping->set)) {
        throw new Core\ApiException("missing get or set indices a index: $index", 6, $this->id, 417);
      }
      $value = $xpath->query($mapping->get);
      Core\Debug::variable($value, 'value');
      $this->{$resultFunc}($mapping->set, $value);
    }

    return new Core\DataContainer($this->result->saveXML(), strtolower($format));
  }

  /**
   * Add to result object.
   *  parents separated by '/'
   *  foo/bar - add the node bar as the child of foo
   *  foo[@bar] = add to node as an attribute <foo bar="value"></foo>
   * @param $regex
   * @param \DOMNodeList $values
   * @throws \Gaterdata\Core\ApiException
   */
  private function _addResultXml($regex, \DOMNodeList $values)
  {
    $queryNodes = explode('/', trim($regex, '/'));
    // $xpath = new \DOMXPath($this->result);
    $oldQuery = '';

    while ($queryNode = array_shift($queryNodes)) {

      $queryNode = preg_replace('/[^A-Za-z0-9\[\]\@]/', '', $queryNode); // strip all non-special characters
      if (empty($queryNode)) {
        continue;
      }
      $newQuery = "$oldQuery/$queryNode";

      if (!empty($queryNodes)) {
        // not the end node, just add node (if not already exists)
        $entries = $xpath->query($newQuery);
        if ($entries->length == 0) {
          $entries = $xpath->query(empty($oldQuery) ? '/' : $oldQuery);
          $node = $this->result->createElement($queryNode);
          $entries->item(0)->appendChild($node);
        }

      } else {
        // this is last one, add node and value
        if (preg_match("/^[a-zA-Z0-9\-_]+$/", $queryNode)) { // /foo/bar
          // add value as with node name as child
          $entries = $xpath->query($newQuery);
          if ($entries->length == 0) {
            // create parent node if needed
            $entries = $xpath->query(empty($oldQuery) ? '/' : $oldQuery);
            $node = $this->result->createElement($queryNode);
            $entries->item(0)->appendChild($node);
            $entries = $xpath->query($newQuery);
          }
          foreach ($values as $value) {
            $entries->item(0)->appendChild($this->result->importNode($value, true));
          }

        } elseif (preg_match("/^[a-zA-Z0-9]+\[\@.+\]$/", $queryNode)) { // /foo/bar[@foobar]
          // add value as attribute of a node
          if ($values->length > 1) {
            throw new Core\ApiException("error, cannot add multiple valies to attribute to XML ($regex).", 6, $this->id, 417 );
          }
          $left = strpos($queryNode, '[');
          $right = strpos($queryNode, ']');
          $nodeName = substr($queryNode, 0, $left);
          $attributeName = substr($queryNode, $left + 2, $right - $left - 2);
          $entries = $xpath->query("$oldQuery/$nodeName");
          if ($entries->length == 0) {
            // create parent node if needed
            $entries = $xpath->query(empty($oldQuery) ? '/' : $oldQuery);
            $node = $this->result->createElement($nodeName);
            $entries->item(0)->appendChild($node);
            $entries = $xpath->query("$oldQuery/$nodeName");
          }
          $entries->item(0)->setAttribute($attributeName, $values->item(0)->nodeValue);
        }
      }
      $oldQuery = $newQuery;
    }
  }
}
