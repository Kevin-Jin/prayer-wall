<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');

class config {
	private static $CFG_FILE = '../../config.ini';

	public $dbHost;
	public $dbUser;
	public $dbPass;
	public $dbName;
	public $bcryptRounds;

	private function __construct($configFile) {
		$props = parse_ini_file($configFile, true);
		$this->dbHost = $props['dbhost'];
		$this->dbUser = $props['dbuser'];
		$this->dbPass = $props['dbpass'];
		$this->dbName = $props['dbname'];
		$this->bcryptRounds = $props['bcryptrounds'];
	}

	private static $instance;

	public static function getInstance() {
		if (self::$instance == null)
			self::$instance = new config(self::$CFG_FILE);
		return self::$instance;
	}
}
?>
