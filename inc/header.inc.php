<?php
use Noodlehaus\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$conf = Config::load(__DIR__.'/../config.yml');

$log = new Logger('main');
$log->pushHandler(new StreamHandler($conf["logFile"], Logger::DEBUG));

$log->debug("Configured",$conf->all());

$client = Elasticsearch\ClientBuilder::create()
    ->setHosts([
        $conf["host"] . ":" . $conf["port"] // IP + Port
    ])
	->build();

$log->debug('Bridge opened');

$dsn = 'mysql:dbname='.$conf["dbName"].';host='.$conf["dbHost"].';charset=utf8';

try {
    $dbh = new PDO($dsn, $conf["dbUser"], $conf["dbPass"]);
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
