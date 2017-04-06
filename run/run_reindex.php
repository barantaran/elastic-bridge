<?php
/**
 * Init reindex procedure:
 * 1. Remove item from elastic index.
 * 2. Mark item as waiting for index.
 * */

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../inc/header.inc.php';

$log->debug('Reindex started');

/* -1 - waiting for index for the first time*/
/* 0 - waiting for index */
/* 1 - indexed, active */
/* 2 - waiting for removal */
/* 3 - waiting for reindex */
/* 4 - removed, not active */

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = ".$conf["indexStatus"]["WAIT_FOR_UPD"];

$source = getSource($sql);

foreach($source as $one)
{
    $response = false;
    $params = [
        'index' => $conf["index"],
        'type' => 'image',
        'id' => $one['file_id']
    ];

    $log->debug("Remove from elastic index", $params);

    try {
        $response = $client->delete($params);
        $log->debug("Elastic response", $response);
    } catch (Exception $e) {
        $elasticError = json_decode($e->getMessage(),1);
        $log->warning("Can't delete item from index", [$elasticError]);
    } finally {
        $sql = "UPDATE file SET ext_index_status = ".$conf["indexStatus"]["WAIT_FOR_IDX"]." WHERE id = " . $one['file_id'];
        if($dbh->query($sql))
            $log->debug("Index status updated", $params);
        else
            $log->error("Index status update failed", $params);
    }
}

$log->debug('Reindex finished');
