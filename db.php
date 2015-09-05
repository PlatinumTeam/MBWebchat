<?php

//Catch any errors
if (defined("MBDBRUN"))
	return;
define("MBDBRUN", 1);

/**
 * Master database control class
 */
class MBDB {
	static $databases = null;

	/**
	 * Get the hostname for the specified database
	 * @param string $db The database's identifier
	 * @return string The database's hostname
	 */
	static function getDatabaseHost($db) {
		return self::$databases[$db]["host"];
	}

	/**
	 * Get the database name for the specified database
	 * @param string $db The database's identifier
	 * @return string The database's name
	 */
	static function getDatabaseName($db) {
		return self::$databases[$db]["data"];
	}

	/**
	 * Get the username to access the specified database
	 * @param string $db The database's identifier
	 * @return string The username to access the database
	 */
	static function getDatabaseUser($db) {
		return self::$databases[$db]["user"];
	}

	/**
	 * Get the password to access the specified database
	 * @param string $db The database's identifier
	 * @return string The password to access the database
	 */
	static function getDatabasePass($db) {
		return self::$databases[$db]["pass"];
	}

	/**
	 * Add a database to the class's list of databases
	 * @param string $ident The database's identifier
	 * @param string $host The host for the database
	 * @param string $data The database name for the database
	 * @param string $user The username to access the database
	 * @param string $pass The password to access the database
	 */
	static function addDatabase($ident = null, $host = null, $data = null, $user = null, $pass = null) {
		if ($ident == null || $host == null || $data == null || $user == null || $pass == null)
			return;
		if (self::$databases == null) {
			self::$databases = array();
		}
		
		//%b -> resolves to getBranch()
		$ident = str_replace("%b", self::getBranch(), $ident);
		$host  = str_replace("%b", self::getBranch(),  $host);
		$data  = str_replace("%b", self::getBranch(),  $data);
		$user  = str_replace("%b", self::getBranch(),  $user);
		$pass  = str_replace("%b", self::getBranch(),  $pass);

		self::$databases[$ident] = array("host" => $host, "data" => $data, "user" => $user, "pass" => $pass);
	}

	/**
	 * Get the current site "branch" (dev | staging | prod)
	 * @return string The current branch
	 */
	static function getBranch() {
		$branch = basename(dirname(__DIR__));
		return $branch;
	}
}

//Default DBs
MBDB::addDatabase("joomla",       "localhost", "%b_joomla",       "mb-%b", "");
MBDB::addDatabase("platinum",     "localhost", "%b_platinum",     "mb-%b", "");
MBDB::addDatabase("platinum_old", "localhost", "%b_platinum_old", "mb-%b", "");
MBDB::addDatabase("dedicated",    "localhost", "%b_dedicated",    "mb-%b", "");
MBDB::addDatabase("fubar",        "localhost", "%b_fubar",        "mb-%b", "");
