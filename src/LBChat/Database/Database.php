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

	public function __construct($name) {
		//Load the db config
		require("../db.php");

		try {
			$dsn = "mysql:dbname=" . \MBDB::getDatabaseName($name) . ";host=" . \MBDB::getDatabaseHost($name);
			$this->connection = new \PDO($dsn, \MBDB::getDatabaseUser($name), \MBDB::getDatabasePass($name));
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