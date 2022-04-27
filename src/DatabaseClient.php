<?php

namespace Fatfingers23\ReplitDatabaseClient;

use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class DatabaseClient
{

    /**
     * Guzzle Http client
     * @var httpClient
     */
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

    /**
     * Sets a value to a key in the database
     * @param string $key
     * @param array|string $value Can be an array or String.
     * @return void
     * @throws GuzzleException
     */
    public function set(string $key, array|string $value): void
    {
        $key = urlencode($key);
        if(is_array($value)){
            $value = json_encode($value);
        }
        $this->webClient->request('POST', "", [
            "body" => "$key=$value"
        ]);
    }

    /**
     * Gets a value by its key
     * @param string $key
     * @return string|array|null
     * @throws GuzzleException
     */
    public function get(string $key): array|string|null
    {
        try {
            $key = urlencode($key);
            $request = $this->webClient->request('GET', getenv('REPLIT_DB_URL') . "/$key");
            $resultAsString = (string)$request->getBody();
            if($this->isJson($resultAsString)){
                return json_decode($resultAsString, true);
            }else{
                return $resultAsString;
            }
        } catch (ClientException $e) {

            $response = $e->getResponse();
            if ($response->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Delete a database value by it's key
     * @param string $key
     * @return void
     * @throws GuzzleException
     */
    public function delete(string $key): void
    {
        try {
            $key = urlencode($key);
            
            $this->webClient->request('DELETE', getenv('REPLIT_DB_URL') . "/$key");
        } catch (ClientException $e) {/**/
            $response = $e->getResponse();
            if ($response->getStatusCode() === 404) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Gets an array of all the keys in a given prefix. If nothing passed in returns all.
     * @param string $prefix
     * @return array Array of key prefixes. Can be a empty array
     * @throws GuzzleException
     */
    public function getPrefixKeys(string $prefix = ''): array
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

    /**
     * Returns all the keys to a given prefix
     * @param string $prefix
     * @return array|null
     * @throws GuzzleException
     */
    public function getPrefix(string $prefix = ''): ?array
    {
        $keys = $this->getPrefixKeys($prefix);
        if ($keys != null && count($keys) == 0) {
            return null;
        }
        $returnArray = [];
        foreach ($keys as $key) {
            $returnArray[$key] = $this->get($key);
        }
        return $returnArray;
    }

    /**
     * Deletes a series of keys by the keys prefix
     * @param string $prefix
     * @return void
     * @throws GuzzleException
     */
    public function deleteByPrefix(string $prefix = ''): void
    {
      $keys = $this->getPrefixKeys($prefix);
      if(count($keys) != 0){
        foreach($keys as $key){
        $this->delete($key);  
        }
      }
    }

    /**
     * Tests to see if a string is json.
     * @param string $string
     * @return bool
     */
    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
