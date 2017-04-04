<?php
/**
 * Init reindex procedure:
 * 1. Remove item from elastic index.
 * 2. Mark item as waiting for index.
 * */

require 'vendor/autoload.php';
require 'header.inc.php';

$log->debug('Reindex started');

/* 0 - waiting for index */
/* 1 - indexed, active */
/* 2 - waiting for removal */
/* 3 - waiting for reindex */
/* 4 - removed, not active */

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = 3";

$source = getSource($sql);

foreach($source as $one)
{
  $params = [
    'index' => $conf["index"],
    'type' => 'image',
    'id' => $one['file_id']
  ];

  $response = $client->delete($params);

  if($response){
    $sql = "UPDATE file SET ext_index_status = 0 WHERE id = " . $one['file_id'];
    $db->query($sql);
  }
}
