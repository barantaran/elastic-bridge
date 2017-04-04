<?php
/**
 * Init index procedure:
 * 1. Find items waiting for index.
 * 2. Push items into elastic index.
 * 3. Mark items as indexed.
 * */

require 'vendor/autoload.php';
require 'header.inc.php';

$log->debug('Index started');

/* 0 - waiting for index */
/* 1 - indexed, active */
/* 2 - waiting for removal */
/* 3 - waiting for reindex */
/* 4 - removed, not active */

$sql = "SELECT * FROM plugin_imageviewer_meta JOIN file ON file_id = file.id WHERE statusId = 1 AND ext_index_status = 0";

$source = getSource($sql);

foreach($source as $one)
{
    $body = json_decode($one["raw_data"],1);
    $bodyFiltered = array();

    //Filter raw metadata according to configuration
    foreach($conf["indexedFields"] as $field){
        if(array_key_exists($field,$body)) $bodyFiltered[mb_strtolower($field)] = $body[$field];
    }

    if(!isset($bodyFiltered)) continue;

    //Collect metadata stored at particular SQL columns but not in the raw_data.
    $sql = "SELECT * FROM file WHERE id =".$one['file_id'];
    $fileSource = getSource($sql)->fetch();

    $bodyFiltered["imdbId"] = $fileSource["shortUrl"];
    $bodyFiltered["plot"] = $fileSource["originalFilename"];
    $bodyFiltered["exifimagelength"] = $one["height"];
    $bodyFiltered["exifimagewidth"] = $one["width"];

    //Generate procedural metadata
    $bodyFiltered["poster"] = "https://photo.mir24.tv/core/cache/plugins/imageviewer/".$fileSource['id']."/".$fileSource['unique_hash']."/585x440_cropped.jpg";

    $bodyFiltered["date_taken"] = date("d/m/Y", strtotime($one["date_taken"]));

    if($one["width"] > $one["height"] ){
        $bodyFiltered["horizontal"] = 1;
        $bodyFiltered["sizetype"] = $one["width"];
    }
    else{
        $bodyFiltered["horizontal"] = 0;
        $bodyFiltered["sizetype"] = $one["height"];
    }

    //Push metadata to elastic index 
    $params = [
        'id' => $fileSource["id"],
        'index' => $conf["index"],
        'type' => 'image',
        'body' => $bodyFiltered
    ];

    $response = $client->index($params);

    //Mark item as indexed if succeed
    if($response){
        $sql = "UPDATE file SET ext_index_status = 1 WHERE id = " . $one['file_id'];
        $db->query($sql);
    }
}
