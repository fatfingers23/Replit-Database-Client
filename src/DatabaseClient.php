<?php

namespace Fatfingers23\ReplitDatabaseClient;

require_once 'vendor/autoload.php';

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\ClientException;

class DatabaseClient
{


  protected httpClient $webClient;

  public function __construct(string $url = null)
  {

    if ($url === null) {
      $url = getenv('REPLIT_DB_URL');
    }

    $this->webClient = new httpClient([
      'base_uri' => $url,
      'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
    ]);
  }

  public function set(string $key, string $value)
  {
    $key = urlencode($key);
    $this->webClient->request('POST', "", [
      "body" => "$key=$value"
    ]);
  }

  public function get(string $key)
  {
    try {
      $key = urlencode($key);
      $request = $this->webClient->request('GET', getenv('REPLIT_DB_URL') . "/$key");
      return (string)$request->getBody();
    } catch (ClientException $e) {

      $response = $e->getResponse();
      if ($response->getStatusCode() === 404) {
        return null;
      }
      throw $e;
    }
  }

  public function delete(string $key)
  {
    try {
      $key = urlencode($key);
      $this->webClient->request('DELETE', getenv('REPLIT_DB_URL') . "/$key");
    } catch (ClientException $e) {
      throw $e;

      $response = $e->getResponse();
      if ($response->getStatusCode() === 404) {
        return;
      }
      throw $e;
    }
  }

  /**
   * @return array Array of key prefixs. Can be a empty array
   */
  public function getPrefixKeys(string $prefix): array
  {
    try {
      $prefix = urlencode($prefix);
      $request = $this->webClient->request('GET', getenv('REPLIT_DB_URL') . "?prefix=$prefix");
      $result = $request->getBody();
      return explode("\n", $result);
    } catch (ClientException $e) {

      $response = $e->getResponse();
      if ($response->getStatusCode() === 404) {
        return array();
      }
      throw $e;
    }
  }

  public function setArray(string $key, array $value)
  {
    $key = urlencode($key);
    $value = json_encode($value);
    $this->webClient->request('POST', "", [
      "body" => "$key=$value"
    ]);
  }

  public function getArray(string $key)
  {
    try {
      $key = urlencode($key);
      $request = $this->webClient->request('GET', getenv('REPLIT_DB_URL') . "/$key");
      $result = $request->getBody();
      return json_decode($result, true);
    } catch (ClientException $e) {

      $response = $e->getResponse();
      if ($response->getStatusCode() === 404) {
        return null;
      }
      throw $e;
    }
  }

  public function getPrefix(string $prefix)
  {
    $keys = $this->getPrefixKeys($prefix);
    if ($keys != null && count($keys) == 0) {
      return null;
    }
    $returnArray = [];
    foreach ($keys as $key) {
      $result = $this->get($key);
    
      if ($this->isJson($result)) {
        $returnArray[$key] =  json_decode($result, true);
      } else {
        $returnArray[$key] =  $result;
      }
    }
    return $returnArray;
  }

  private function isJson($string)
  {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }
}
