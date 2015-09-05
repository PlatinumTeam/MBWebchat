<?php
namespace LBChat\Database;

use LBChat\ChatServer;
use Ratchet\ConnectionInterface;

class SQLChatServer extends ChatServer {
	/**
	 * @var Database $database
	 */
	protected $database;

	public function __construct($database) {
		parent::__construct();

		$this->database = $database;
		$this->initDatabase();
	}

	protected function addClient(ConnectionInterface $conn) {
		$client = new SQLChatClient($this, $conn, $this->database);
		$this->connections->attach($conn, $client);
		$this->clients->attach($client);
	}

	protected function initDatabase() {
		$this->database->prepare("TRUNCATE TABLE `loggedin`")->execute();
		$this->database->prepare("TRUNCATE TABLE `jloggedin`")->execute();
	}

}