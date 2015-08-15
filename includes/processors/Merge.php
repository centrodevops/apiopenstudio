<?php

/**
 * Perform merge of two external sources
 *
 * METADATA
 * {
 *    "type":"merge",
 *    "meta":{
 *      "mergeType":"union",
 *      "sources":[
 *        {"type":"input","meta":{"url":"http://data1.com"}},
 *        {"type":"input","meta":{"url":"http://data2.com"}},
 *      ]
 *    }
 *  }
 */

namespace Datagator\Processors;
use Datagator\Core;

class Merge extends ProcessorBase
{
  private $_defaultType = 'union';
  protected $required = array('sources');

  public function process()
  {
    Core\Debug::variable($this->meta, 'processor Merge', 4);
    $this->validateRequired();

    $sources = $this->meta->sources;
    $values = array();
    foreach ($sources as $source) {
      $processor = $this->getProcessor($source);
      $data = $processor->process();
      $values[] = $data;
    }

    $type = empty($this->meta->meta->mergeType) ? $this->_defaultType : $this->meta->meta->mergeType;
    $type = ucfirst(trim($type));
    $method = "_merge$type";
    if (method_exists($this, $method)) {
      $result = $this->$method($values);
    } else {
      throw new Core\ApiException("invalid mergeType: $type", 3, $this->id, 407);
    }

    return $result;
  }

  private function _mergeNegate($values)
  {
    $result = array_shift($values);
    foreach ($values as $value) {
      $result = array_diff($result, $value);
    }
    return $result;
  }

  private function _mergeUnion($values)
  {
    $result = array_shift($values);
    $result = is_array($result) ? $result : array($result);
    foreach ($values as $value) {
      if (!is_array($value)) {
        $result[] = $value;
      } else {
        $result += $value;
      }
    }
    return $result;
  }

  private function _mergeIntersect($values)
  {
    $result = array_shift($values);
    foreach ($values as $value) {
      $result = array_intersect($result, $value);
    }
    return $result;
  }
}