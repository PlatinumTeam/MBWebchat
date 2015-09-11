<?php
namespace LBChat\Database;

use LBChat\ChatServer;
use LBChat\Integration\IUserSupport;
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
	 * @var IUserSupport $support
	 */
	protected $support;

	/**
	 * @param array $databases
	 */
	public function __construct($databases, IUserSupport $support) {
		parent::__construct();

		$this->databases = $databases;
		$this->support = $support;
		$this->initDatabase();
	}

	public function start() {
		parent::start();

		//Keep-alive loop so we don't drop any connections
		$this->scheduleLoop(60, function() {
			$this->keepAlive();
		});
	}

	protected function createClient(ConnectionInterface $conn) {
		return new SQLChatClient($this, $conn, $this->databases, $this->support);
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

	protected function keepAlive() {
		foreach ($this->databases as $database) {
			/* @var Database $database */
			try {
				$database->prepare("SELECT 'keep-alive'")->execute();
			} catch (\PDOException $e) {

			}
		}
	}
}