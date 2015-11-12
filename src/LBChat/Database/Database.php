<?php
namespace LBChat\Database;

/**
 * A simple database access wrapper class using the MBDB database system.
 * Class Database
 * @package LBChat\Database
 */
class Database {
	/**
	 * @var \PDO $connection
	 */
	protected $connection;

	const EMULATION_MAXIMUM_VERSION = "5.1.17";

	public function __construct($name) {
		//Load the db config
		require("../db.php");

		try {
			$dsn = "mysql:dbname=" . \MBDB::getDatabaseName($name) . ";host=" . \MBDB::getDatabaseHost($name);
			$this->connection = new \PDO($dsn, \MBDB::getDatabaseUser($name), \MBDB::getDatabasePass($name));

			//Set queries to emulate if under MySQL 5.1.17 for performance reasons.
			// Via http://stackoverflow.com/a/10455228/214063
			$serverVersion = $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
			$emulate = (version_compare($serverVersion, self::EMULATION_MAXIMUM_VERSION, "<"));
			$this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $emulate);

		} catch (\Exception $e) {
			//Something
			throw $e;
		}
	}

	/**
	 * Create a prepared statement using the database.
	 * @param string $query The query to prepare
	 * @return \PDOStatement
	 */
	public function prepare($query) {
		return $this->connection->prepare($query);
	}
}