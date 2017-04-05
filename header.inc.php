<?php
use Noodlehaus\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('main');
$log->pushHandler(new StreamHandler('main.log', Logger::DEBUG));

$conf = Config::load('config.yml');

$log->debug("Configured",$conf->all());

$client = Elasticsearch\ClientBuilder::create()
    ->setHosts([
        $conf["host"] . ":" . $conf["port"] // IP + Port
    ])
	->build();

$log->debug('Bridge opened');

$dsn = 'mysql:dbname=photobank;host=172.17.0.2;charset=utf8';
$user = 'photobank_user';
$password = 'GjkmLbhfr2016MS';

try {
    $dbh = new PDO($dsn, $user, $password);
    $log->debug('Connected to source!');
} catch (PDOException $e) {
    $log->error('DB connection failed!');
    $log->error($e->getMessage());
}

function getSource($sql)
{
	global $dbh;
	$res = $dbh->query($sql, PDO::FETCH_ASSOC);

	return $res;
}
