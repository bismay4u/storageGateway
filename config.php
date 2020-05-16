<?php
if(!defined("ROOT")) die("This file can't be accessed directly");

error_reporting(E_ALL);
ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

$S3Config=[
  "test-dev-data"=>[
  		"accessKeyId"=> '', 
	    "secretAccessKey"=> '',
	    "bucket"=>"bucket1",
	    "folder"=>"dev",
	    "bucket_security_policy"=> S3::ACL_PUBLIC_READ,
	    "security_policy"=> S3::ACL_PUBLIC_READ,
	    "targetURI"=>"https://pixy-dev.s3.us-east-2.amazonaws.com/"
  	]
];

$_ENV['TEMPDIR'] = __DIR__."/tmp/";
$_ENV['POSTDATAPARAM'] = "data";
?>