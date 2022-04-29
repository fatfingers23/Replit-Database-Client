<?php

use Fatfingers23\ReplitDatabaseClient\DatabaseClient;

beforeEach(function () {
    $this->client = new DatabaseClient();
    $this->client->deleteByPrefix();
});

afterEach(function () {
    $this->client->deleteByPrefix();
}); 


test('Set', function () {
    $this->client->set('aKey', 'aValue');
    $fromDatabase = file_get_contents(getenv('REPLIT_DB_URL') . '/aKey');
    $this->assertEquals('aValue', $fromDatabase);
});


test('Get', function () {

    $opts = array(
        'http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => 'test=test value'
            )
    );

    $context = stream_context_create($opts);
    file_get_contents(getenv('REPLIT_DB_URL'), false, $context);

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
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
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
    $keys = ['user.1', 'user.2'];
    foreach ($keys as $key) {
        $opts = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => "$key=test"
                )
        );

        $context = stream_context_create($opts);
        file_get_contents(getenv('REPLIT_DB_URL'), false, $context);
    }

    $result = $this->client->getPrefixKeys('user');
    foreach ($keys as $key) {
        $this->assertTrue(in_array($key, $result));
    }
});


test('Set Array', function () {
    $testArray = ["key" => "value"];
    $this->client->set('arrayKey', $testArray);
    $fromDatabase = file_get_contents(getenv('REPLIT_DB_URL') . '/arrayKey');
    $this->assertEquals($fromDatabase, '{"key":"value"}');
});

test('Get Array', function () {

    $key = "testArrayGet";
    $value = '{"key":"value"}';
    $opts = array(
        'http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $key . '=' . $value
            )
    );

    $context = stream_context_create($opts);
    file_get_contents(getenv('REPLIT_DB_URL'), false, $context);

    $testArray = ["key" => "value"];
    $result = $this->client->get($key, $testArray);
    
    $this->assertArrayHasKey("key", $result);
    $this->assertEquals('value', $result['key']);

});

test('Get Prefix Values', function () {
    $keys = ['city.1', 'city.2'];
    foreach ($keys as $key) {
        $opts = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
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
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $key . '={"key": "value"}'
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


test('Prefix Keys Return Empty Array If No Keys With Prefx', function () {
    $result = $this->client->getPrefixKeys();
    $this->assertCount(0,$result);
});

test('Prefix Get Return Null If No Keys With Prefx', function () {
    $result = $this->client->getPrefix('test');
    $this->assertNull($result);
});