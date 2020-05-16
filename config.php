<?php
if(!defined("ROOT")) die("This file can't be accessed directly");

error_reporting(E_ALL);
ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$S3Config=[
  "test-dev-data"=>[
    "accessKeyId"=> '', 
    "secretAccessKey"=> '',
    "bucket"=>"bucket01",
    "folder"=>"test1",
    "targetURI"=>""
  ]
];

$_ENV['TEMPDIR'] = __DIR__."/tmp/";
$_ENV['POSTDATAPARAM'] = "data";
?>