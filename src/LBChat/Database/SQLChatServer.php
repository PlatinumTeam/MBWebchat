<?php
namespace LBChat\Database;

use LBChat\ChatServer;
use Ratchet\ConnectionInterface;

class SQLChatServer extends ChatServer {
	/**
	 * @var array $databases
	 */
	protected $databases;

	public function __construct($databases) {
		parent::__construct();

		$this->databases = $databases;
		$this->initDatabase();
	}

	protected function addClient(ConnectionInterface $conn) {
		$client = new SQLChatClient($this, $conn, $this->databases);
		$this->connections->attach($conn, $client);
		$this->clients->attach($client);
	}

	protected function initDatabase() {
		$this->db("platinum")->prepare("TRUNCATE TABLE `loggedin`")->execute();
		$this->db("platinum")->prepare("TRUNCATE TABLE `jloggedin`")->execute();
	}

	/**
	 * @param $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}

}