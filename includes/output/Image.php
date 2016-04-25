<?php

/**
 * Output processor for images.
 *
 * Accepts filepath, or remote URL.
 * This will return the actual image, not the URL.
 */

namespace Datagator\Output;
use Datagator\Core;

class Image extends Output
{
  protected $details = array(
    'name' => 'Image',
    'description' => 'Output in image format. The data fed into the output can be a URL (must start with http) or an input filename.',
    'menu' => 'Output',
    'application' => 'All',
    'input' => array(),
  );

  protected function getData()
  {
  }

  public function process()
  {
    parent::process();

    if (!is_string($this->data)) {
      throw new Core\ApiException('data revieved is not an image.', 1, $this->id);
    }
    if (empty($this->data)) {
      throw new Core\ApiException('image empty.', 1, $this->id);
    }

    if (substr($this->data, 0, 4 ) === "http") {
      $curl = new Core\Curl();
      $image = $curl->get($this->data, array('CURLOPT_SSL_VERIFYPEER' => 0, 'CURLOPT_FOLLOWLOCATION' => 1));
      header('Content-Type:' . $curl->type);
      return $image;
    }

    if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $this->data);
    } elseif (function_exists('mime_content_type')) {
      $mime = mime_content_type($this->data);
    } else {
      throw new Core\ApiException('Cannot read mime type of image. Please enable filetype extension.', 1, $this->id);
    }

    header("Content-Type:$mime");
    return file_get_contents($this->data);
  }
}
