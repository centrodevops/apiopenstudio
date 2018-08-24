<?php

/**
 * Fetch  bearer token from the Header
 */

namespace Datagator\Security;
use Datagator\Core;

class BearerToken extends Core\ProcessorEntity
{
  protected $details = array(
    'name' => 'Bearer Token',
    'machineName' => 'bearerToken',
    'description' => 'Fetch a bearer token from the request header.',
    'menu' => 'Security',
    'application' => 'Common',
    'input' => array(),
  );

  public function process()
  {
    Core\Debug::variable($this->meta, 'Processor BearerToken', 4);

    $headers = '';

    if (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      //Nginx or fast CGI
      $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();
      // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
      $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
      if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
      }
    }

    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }

    return '';
  }
}