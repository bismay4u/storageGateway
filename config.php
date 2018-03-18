<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$S3Config=[
  "test-dev-data"=>[
    "accessKeyId"=> '', 
    "secretAccessKey"=> '',
    "bucket"=>"bucket01",
    "folder"=>"test1",
    "targetURI"=>""
  ]
];

$tempDir=__DIR__."/tmp/";
$POSTDATAPARAM="data";
?>