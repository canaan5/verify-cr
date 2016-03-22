<?php
/**
 * Description of configs
 *
 * @author Yehuda Daniel Korotkin
 */
class Config {
	/**
	 * Agent name
	 * @var string
	 */
	public static $agent_name = 'verifyng';
	/**
	 * Agent host
	 * @var string
	 */
	public static $agent_host = 'http://verifyng.dev';
	/**
	 * DB CONFIGS
	 * @var array
	 */
	public static $db_configs = array(
		'read' => array(
			'connection_string' => 'mysql:host=127.0.0.1;dbname=test_verifyng',
			'username' => 'kesty',
			'password' => 'CanaaN55*',
			'port' => '3306',
		),
		'write' => array(
			'connection_string' => 'mysql:host=127.0.0.1;dbname=test_verifyng',
			'username' => 'kesty',
			'password' => 'CanaaN55*',
			'port' => '3306',
		),
		'fulltext-write' => array(
			'connection_string' => 'mysql:host=127.0.0.1;dbname=test_verifyng',
			'username' => 'kesty',
			'password' => 'CanaaN55*',
			'port' => '3306',
		),
	);
	// public static $db_configs = array(
	//     'read'=>array(
	//         'connection_string'=>'mysql:host=192.168.0.111;dbname=verifyng',
	//         'username'=>'kesty',
	//         'password'=>'canaan',
	//         'port'=>'3306'
	//     ),
	//     'write'=>array(
	//         'connection_string'=>'mysql:host=192.168.0.111;dbname=verifyng',
	//         'username'=>'kesty',
	//         'password'=>'canaan',
	//         'port'=>'3306'
	//     ),
	//     'fulltext-write'=>array(
	//         'connection_string'=>'mysql:host=192.168.0.111;dbname=verifyng',
	//         'username'=>'kesty',
	//         'password'=>'canaan',
	//         'port'=>'3306'
	//     )
	// );

}
