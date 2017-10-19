<?php
/**
 * Test index connection:
 * */

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../inc/header.inc.php';

$log->debug('Index test started');

$params = [
    'index' => $conf["index"],
    'type' => $conf["types"][0],
    'body' => [
        'query' => [
            'match' => [
                'testField' => 'a'
            ]
        ]
    ]
];

$log->debug("Goin to test index", $params);

$response = $client->search($params);

$log->debug("Elastic response", $response);

$log->debug('Index test finished');
