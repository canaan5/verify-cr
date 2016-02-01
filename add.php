<?php


include 'libs/general.php';

$urlArray=array(
    'http://jimtechs.com/' => "Mobile Tech World"
	);

	Providers::insert_url_list($urlArray, false);