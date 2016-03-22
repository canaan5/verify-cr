<?php

include 'libs/general.php';

$urlArray = array(
	'http://jobberman.com/' => "Jobberman",
	'https://ngcareers.com/' => "Nigeria Carrers",
	'http://www.careers24.com.ng/' => "Carreer24",
);

Providers::insert_url_list($urlArray);

?>