<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Command\Server\NotifyCommand;
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
	 * @var int $lastNotificationId
	 */
	protected $lastNotificationId;

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
		//Check for new notifications every second in the database
		$this->scheduleLoop(1, function() {
			$this->checkNotifications();
		});
	}

	/**
	 * Adds a client to the internal client list. This is overridden so we can
	 * create SQLChatClients instead of normal clients.
	 * @param ConnectionInterface $conn
	 * @return ChatClient
	 */
	protected function createClient(ConnectionInterface $conn) {
		return new SQLChatClient($this, $conn, $this->getUserSupport(), $this->databases);
	}

	/**
	 * Initialize the databases, clearing out any old sessions
	 */
	protected function initDatabase() {
		$this->db("platinum")->prepare("TRUNCATE TABLE `loggedin`")->execute();
		$this->db("platinum")->prepare("TRUNCATE TABLE `jloggedin`")->execute();

		$this->updateNotificationId();
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

	protected function checkNotifications() {
		//Query the database for any previous notifications
		$query = $this->db("platinum")->prepare("SELECT * FROM `notify` WHERE `id` >= :id");
		$query->bindParam(":id", $this->lastNotificationId);
		$query->execute();

		//Run through all rows and make notifications for them
		$rows = $query->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			/* @var array $row */
			$username = $row["username"];
			$type = $row["type"];
			$access = $row["access"];
			$message = $row["message"];

			//Find the client, or make a dummy one if they're not online
			$client = $this->findClient($username);
			if ($client === null) {
				$client = new DummyChatClient($this, $username, 0);
			}

			//Send a notification to everyone
			$command = new NotifyCommand($this, $client, $type, $access, $message);
			$this->broadcastCommand($command);
		}

		$this->updateNotificationId();
	}

	protected function updateNotificationId() {
		//Get the last row in the notification table
		$query = $this->db("platinum")->prepare("SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = :schema AND `TABLE_NAME` = 'notify' LIMIT 1");
		$query->bindParam(":schema", $this->db("platinum")->getSchema());
		$query->execute();
		$this->lastNotificationId = $query->fetchColumn(0);
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

	/**
	 * Ban a client, preventing them from joining for a number of days
	 * @param ChatClient $client The client to ban
	 * @param int        $days   How many days they are banned for, or -1 if indefinite
	 */
	public function banClient(ChatClient $client, $days) {
		//Submit this to the server
		$query = $this->db("platinum")->prepare("UPDATE `users` SET `access` = -3, `banned` = 1 WHERE `username` = :username");
		$query->bindParam(":username", $client->getUsername());
		$query->execute();
		$client->disconnect();
	}
}