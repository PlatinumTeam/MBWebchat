<?php
namespace LBChat\Database;

use LBChat\ChatServer;
use Ratchet\ConnectionInterface;

/**
 * Class SQLChatServer
 * An extended Chat Server that interfaces with the databases on MarbleBlast.com
 * @package LBChat\Database
 */
class SQLChatServer extends ChatServer {
	/**
	 * @var array $databases
	 */
	protected $databases;

	/**
	 * @param array $databases
	 */
	public function __construct($databases) {
		parent::__construct();

		$this->databases = $databases;
		$this->initDatabase();
	}

	/**
	 * Adds a client to the internal client list. This is overridden so we can
	 * create SQLChatClients instead of normal clients.
	 * @param ConnectionInterface $conn
	 */
	protected function addClient(ConnectionInterface $conn) {
		$client = new SQLChatClient($this, $conn, $this->databases);
		$this->connections->attach($conn, $client);
		$this->clients->attach($client);
	}

	/**
	 * Initialize the databases, clearing out any old sessions
	 */
	protected function initDatabase() {
		$this->db("platinum")->prepare("TRUNCATE TABLE `loggedin`")->execute();
		$this->db("platinum")->prepare("TRUNCATE TABLE `jloggedin`")->execute();
	}

	/**
	 * Get a specific database by name
	 * @param string $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}

}