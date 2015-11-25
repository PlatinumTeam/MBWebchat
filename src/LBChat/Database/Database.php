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

	/**
	 * @var string $host
	 */
	protected $host;

	/**
	 * @var string $username
	 */
	protected $username;

	/**
	 * @var string $name
	 */
	protected $name;

	/**
	 * @var string $schema
	 */
	protected $schema;

	const EMULATION_MAXIMUM_VERSION = "5.1.17";

	public function __construct($name) {
		//Load the db config
		require("../db.php");

		$this->name = $name;
		$this->schema = \MBDB::getDatabaseName($name);
		$this->host = \MBDB::getDatabaseHost($name);
		$this->username = \MBDB::getDatabaseUser($name);

		try {
			$dsn = "mysql:dbname=" . $this->schema . ";host=" . $this->host;
			$this->connection = new \PDO($dsn, $this->username, \MBDB::getDatabasePass($name));

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

	/**
	 * Get the database's username
	 * @return string The database's username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Get the database's name
	 * @return string The database's name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the database's schema
	 * @return string The database's schema
	 */
	public function getSchema() {
		return $this->schema;
	}

	/**
	 * Get the database's host
	 * @return string The database's host
	 */
	public function getHost() {
		return $this->host;
	}
}