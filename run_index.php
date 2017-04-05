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
$log->debug("Configured",$conf->all());

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

    $log->debug("Got raw data", $body);

    //Filter raw metadata according to configuration
    if($body != null) {
        foreach($conf["indexedFields"] as $field){
            $log->debug("Filtering field $field");
            if(array_key_exists($field,$body)) $bodyFiltered[mb_strtolower($field)] = $body[$field];
            else $log->debug("Field $field not found");
        }
    } else {
        $log->warning("raw_data is empty", $one);
    }

    $log->debug("Filtered raw data", $bodyFiltered);

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

    $log->debug("Goin to push into index", $params);

    $response = $client->index($params);

    $log->debug("Elastic response", $response);

    //Mark item as indexed if succeed
    if($response){
        $sql = "UPDATE file SET ext_index_status = 1 WHERE id = " . $one['file_id'];
        if($dbh->query($sql))
            $log->debug("Index status updated", $params);
        else
            $log->error("Index status update failed", $params);
    }
}
