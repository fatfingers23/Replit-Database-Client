<?php

use Fatfingers23\ReplitDatabaseClient\DatabaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

beforeEach(function () {
  $this->client = new DatabaseClient();
});

test('Set', function () {

  $result = $this->client->set('aKey', 'aValue');
  $fromDatabase = file_get_contents(getenv('REPLIT_DB_URL') . '/aKey');
  $this->assertEquals('aValue', $fromDatabase);
});


test('Get', function () {

  $opts = array(
    'http' =>
    array(
      'method'  => 'POST',
      'header'  => 'Content-Type: application/x-www-form-urlencoded',
      'content' => 'test=test value'
    )
  );

  $context  = stream_context_create($opts);
  $result = file_get_contents(getenv('REPLIT_DB_URL'), false, $context);

  $result = $this->client->get('test');
  $this->assertEquals('test value', $result);
});


test('Get is null', function () {
  $result = $this->client->get('a fake key');
  $this->assertNull($result);
});


test('Delete', function () {
  $key = "deletedKey";
  $opts = array(
    'http' =>
    array(
      'method'  => 'POST',
      'header'  => 'Content-Type: application/x-www-form-urlencoded',
      'content' => "$key=test"
    )
  );

  $context = stream_context_create($opts);
  file_get_contents(getenv('REPLIT_DB_URL'), false, $context);
  $this->client->delete($key);
  $headers = get_headers(getenv('REPLIT_DB_URL') . "/$key");
  $statusCode = substr($headers[0], 9, 3);
  $this->assertEquals(404, intval($statusCode));
});


test('Prefix', function () {
  $prefix = 'user.';
  $keys = ['user.1', 'user.2'];
  foreach ($keys as $key) {
    $opts = array(
      'http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => "$key=test"
      )
    );

    $context = stream_context_create($opts);
    file_get_contents(getenv('REPLIT_DB_URL'), false, $context);
  }

  $result = $this->client->getPrefixKeys('user');
  foreach ($keys as $key) {
    $this->assertTrue(in_array($key, $keys));
  }
});


test('Set Array', function () {
  $testArray = ["key" => "value"];
  $this->client->setArray('arrayKey', $testArray);
  $fromDatabase = file_get_contents(getenv('REPLIT_DB_URL') . '/arrayKey');
  $this->assertEquals($fromDatabase, '{"key":"value"}');
});

test('Get Array', function () {

  $key = "testArrayGet";
  $vlaue = '{"key":"value"}';
  $opts = array(
    'http' =>
    array(
      'method'  => 'POST',
      'header'  => 'Content-Type: application/x-www-form-urlencoded',
      'content' => $key . '=' . $vlaue
    )
  );

  $context  = stream_context_create($opts);
  $result = file_get_contents(getenv('REPLIT_DB_URL'), false, $context);

  $testArray = ["key" => "value"];
  $result = $this->client->getArray('arrayKey', $testArray);
  
  $this->assertArrayHasKey("key", $result);
  $this->assertEquals('value', $result['key']);
  
});

test('Get Prefix Values', function () {
  $keys = ['city.1', 'city.2'];
  foreach ($keys as $key) {
    $opts = array(
      'http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => "$key=test"
      )
    );

    $context = stream_context_create($opts);
    file_get_contents(getenv('REPLIT_DB_URL'), false, $context);
  }
  $result = $this->client->getPrefix('city');
  $this->assertIsArray($result);
  foreach ($keys as $key) {
    $this->assertArrayHasKey($key, $result);
    $this->assertEquals('test', $result[$key]);
  }
});


test('Get Prefix Arrays', function () {
  $keys = ['cake.1', 'cake.2'];
  foreach ($keys as $key) {
    $opts = array(
      'http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $key.'={"key": "value"}'
      )
    );

    $context = stream_context_create($opts);
    file_get_contents(getenv('REPLIT_DB_URL'), false, $context);
  }
  $result = $this->client->getPrefix('cake');
  $this->assertIsArray($result);
  foreach ($keys as $key) {
    $this->assertArrayHasKey($key, $result);
    $this->assertArrayHasKey('key', $result[$key]);
  }
});