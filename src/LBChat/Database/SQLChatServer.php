<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Integration\IServerSupport;
use LBChat\Integration\IUserSupport;
use LBChat\Misc\DummyChatClient;
use LBChat\Misc\ServerChatClient;
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
	 * @param IUserSupport $support
	 */
	public function __construct(IServerSupport $serverSupport, IUserSupport $userSupport, $databases) {
		parent::__construct($serverSupport, $userSupport);

		$this->databases = $databases;
		$this->initDatabase();
	}

	public function start() {
		parent::start();

		//Keep-alive loop so we don't drop any connections
		$this->scheduleLoop(60, function() {
			$this->keepAlive();
		});
	}

	/**
	 * Adds a client to the internal client list. This is overridden so we can
	 * create SQLChatClients instead of normal clients.
	 * @param ConnectionInterface $conn
	 */
	protected function addClient(ConnectionInterface $conn) {
		$client = new SQLChatClient($this, $conn, $this->getUserSupport(), $this->databases);
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

	protected function keepAlive() {
		foreach ($this->databases as $database) {
			/* @var Database $database */
			try {
				$database->prepare("SELECT 'keep-alive'")->execute();
			} catch (\PDOException $e) {

			}
		}
	}

	/**
	 * Callback method for when a client finishes logging in
	 * @param ChatClient $client The client who is logging in
	 * @return boolean If the client should be disconnected
	 */
	public function onClientLogin(ChatClient $client) {
		if (!parent::onClientLogin($client))
			return false;

		//Send this client the last 20 messages

		$query = $this->db("platinum")->prepare("
			SELECT * FROM (
				SELECT * FROM `chat` WHERE `location` >= 0 AND `access` >= 0 AND `access` < 3 AND `destination` = '' ORDER BY `id` DESC LIMIT 20
			) AS `messagesReverse` ORDER BY `id` ASC
		");
		$query->execute();

		$rows = $query->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			/* @var array $row */
			$command = new ChatCommand($this, new DummyChatClient($this, "[Old] {$row["username"]}", $row["access"]), $client, $row["message"]);
			$command->execute($client);
		}

		ServerChatClient::sendMessage(false, $client, "Previous 20 Chat Messages:");
		ServerChatClient::sendMessage(false, $client, "----------------------------------");

		return true;
	}

}