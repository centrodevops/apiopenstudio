<?php

/**
 * Fetch and save resource data.
 */

namespace Datagator\Db;
use Datagator\Core;

class ResourceMapper
{
  protected $db;

  /**
   * @param $dbLayer
   */
  public function __construct($dbLayer)
  {
    $this->db = $dbLayer;
  }

  /**
   * @param \Datagator\Db\Resource $resource
   * @return bool
   * @throws \Datagator\Core\ApiException
   */
  public function save(Resource $resource)
  {
    if ($resource->getId() == NULL) {
      $sql = 'INSERT INTO resource (`appid`, `method`, `identifier`, `meta`, `ttl`) VALUES (?, ?, ?, ?, ?)';
      $bindParams = array(
        $resource->getAppId(),
        $resource->getMethod(),
        $resource->getIdentifier(),
        $resource->getMeta(),
        $resource->getTtl()
      );
      $result = $this->db->Execute($sql, $bindParams);
    } else {
      $sql = 'UPDATE resource SET `appid` = ?, `method` = ?, `identifier` = ?, `meta` = ?, `ttl` = ? WHERE `id` = ?';
      $bindParams = array(
        $resource->getAppId(),
        $resource->getMethod(),
        $resource->getIdentifier(),
        $resource->getMeta(),
        $resource->getTtl(),
        $resource->getId()
      );
      $result = $this->db->Execute($sql, $bindParams);
    }
    if (!$result) {
      throw new Core\ApiException($this->db->ErrorMsg());
    }
    return TRUE;
  }

  public function delete($resource)
  {
    if ($resource->getId() == NULL) {
      throw new Core\ApiException('could not delete resource, empty id');
    }
    $sql = 'DELETE FROM `resource` WHERE `id` = ?';
    $bindParams = array($resource->getId());
    $result = $this->db->Execute($sql, $bindParams);
    if (!$result) {
      throw new Core\ApiException($this->db->ErrorMsg());
    }
    return TRUE;
  }

  /**
   * @param $id
   * @return \Datagator\Db\Resource
   */
  public function findId($id)
  {
    $sql = 'SELECT * FROM resource WHERE `id` = ?';
    $bindParams = array($id);
    $row = $this->db->GetRow($sql, $bindParams);
    return $this->mapArray($row);
  }

  /**
   * @param $appId
   * @param $method
   * @param $identifier
   * @return \Datagator\Db\Resource
   */
  public function findByAppIdMethodIdentifier($appId, $method, $identifier)
  {
    $sql = 'SELECT * FROM `resource` WHERE `appid` = ? AND `method` = ? AND `identifier` = ?';
    $bindParams = array($appId, $method, $identifier);
    $row = $this->db->GetRow($sql, $bindParams);
    return $this->mapArray($row);
  }

  /**
   * @param $appId
   * @return array
   */
  public function findByAppId($appId)
  {
    $sql = 'SELECT * FROM resource WHERE `appid` = ?';
    $bindParams = array($appId);
    $recordSet = $this->db->Execute($sql, $bindParams);

    $entries   = array();
    while (!$recordSet->EOF) {
      $entries[] = $this->mapArray($recordSet->fields);
    }

    return $entries;
  }

  /**
   * @param array $row
   * @return \Datagator\Db\Resource
   */
  protected function mapArray(array $row)
  {
    $resource = new Resource();

    $resource->setId(!empty($row['id']) ? $row['id'] : NULL);
    $resource->setAppId(!empty($row['appid']) ? $row['appid'] : NULL);
    $resource->setMethod(!empty($row['method']) ? $row['method'] : NULL);
    $resource->setIdentifier(!empty($row['identifier']) ? $row['identifier'] : NULL);
    $resource->setMeta(!empty($row['meta']) ? $row['meta'] : NULL);
    $resource->setTtl(!empty($row['ttl']) ? $row['ttl'] : NULL);

    return $resource;
  }
}