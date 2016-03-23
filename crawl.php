<?php

include 'libs/general.php';

CronManager::init(__FILE__, 10);

try
{
	// GET URLs
	$urls = Providers::get_lead_urls();

	_w('Got ' . count($urls) . ' urls');

	/// PROCESS URLS
	foreach ($urls as $url) {
		// Parse array to url
		$url_w = urls::create_url($url);

		_w('Getting url ' . $url_w);

		// Init content analyzer
		$ca = ContentAnalyzer::getAnalyzer($url_w);

		// If content ignored
		if (!$ca) {
			_w('ignored');

			// Skip
			continue;
		}

//		_w($ca->getOgDescription());
//		die;

		_w('Createing general CA data');

		_w('populating search table');
		if (Providers::create_search_item($ca)) {
			_w("search data inserted");

		} else {
			_w("unable to insert search data");
		}

		_w('setting status to indexed');
		if (Providers::change_url_status($url_w, Providers::URLS_TYPE_INDEXED)) {
			_w('status changed to INDEXED');
		} else {
			_w('unable to change status');
		}

//		_w($ca->getLinks());
//		die;

		_w('inserting all other urls');
		Providers::insert_url_list($ca->getLinks());

	}
} catch (Exception $ex) {
	_w('WAS ERROR !!! ' . $ex->getMessage());
}

_w('Done for now');
unset($urls);
