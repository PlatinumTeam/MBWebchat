<?php
namespace LBChat\Database;

class Database {
	/**
	 * @var \PDO $connection
	 */
	protected $connection;

	public function connect() {
		//Load the db config
		require("../db.php");

		try {
			$dsn = "mysql:dbname=" . \MBDB::getDatabaseName("platinum") . ";host=" . \MBDB::getDatabaseHost("platinum");
			$this->connection = new \PDO($dsn, \MBDB::getDatabaseUser("platinum"), \MBDB::getDatabasePass("platinum"));
		} catch (\Exception $e) {
			//Something
			throw $e;
		}
	}

	public function prepare($query) {
		return $this->connection->prepare($query);
	}
}