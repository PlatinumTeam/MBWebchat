<?php
namespace LBChat;
use LBChat\Command\Server;
use LBChat\Command\Server\IServerCommand;
use LBChat\Integration\IServerSupport;
use LBChat\Integration\IUserSupport;
use LBChat\Misc\ServerChatClient;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

/**
 * Class ChatServer
 * A basic chat server implementation that manages clients distributes commands.
 * @package LBChat
 */
class ChatServer implements MessageComponentInterface {
	protected $connections;
	protected $clients;

	protected $serverSupport;
	protected $userSupport;

	protected $kickedClients;
	protected $bannedClients;

	/**
	 * @var LoopInterface $scheduler
	 */
	protected $scheduler;

	public function __construct(IServerSupport $serverSupport, IUserSupport $userSupport) {
		$this->serverSupport = $serverSupport;
		$this->userSupport = $userSupport;

		$this->connections = new \SplObjectStorage();
		$this->clients = new \SplObjectStorage();

		$this->kickedClients = array();
		$this->bannedClients = array();

		ServerChatClient::create($this);
		$this->connections->attach(ServerChatClient::getConnection(), ServerChatClient::getClient());
		$this->clients->attach(ServerChatClient::getClient());
	}

	/**
	 * Start the one-second timer. Needs to be done after we get a scheduler assigned.
	 */
	public function start() {
		$this->scheduleLoop(1, function() {
			foreach ($this->connections as $conn) {
				$client = $this->resolveClient($conn);
				$client->onSecondAdvance();
			}
		});
	}

	/**
	 * Assign the server a loop scheduler for scheduling events
	 * @param LoopInterface $scheduler
	 */
	public function setScheduler(LoopInterface $scheduler) {
		$this->scheduler = $scheduler;
	}

	/**
	 * Called whenever a new client joins the server.
	 * @param ConnectionInterface $conn
	 */
	public function onOpen(ConnectionInterface $conn) {
		$this->addClient($conn);
	}

	/**
	 * Called whenever a client sends a message to the server
	 * @param ConnectionInterface $conn
	 * @param string              $msg
	 */
	public function onMessage(ConnectionInterface $conn, $msg) {
		$from = $this->resolveClient($conn);

		//Split the message into lines
		$lines = explode("\n", $msg);

		foreach ($lines as $line) {
			//Ignore blank lines
			if ($line === "")
				continue;

			$from->interpretMessage($line);
		}
	}

	/**
	 * Called whenever a client disconnects from the server
	 * @param ConnectionInterface $conn
	 */
	public function onClose(ConnectionInterface $conn) {
		$client = $this->resolveClient($conn);
		$client->onLogout();

		$this->removeClient($conn);
	}

	/**
	 * Called whenever there is a connection error
	 * @param ConnectionInterface $conn
	 * @param \Exception          $e
	 */
	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}

	/**
	 * When a client connects, this method adds them to the internal clients list
	 * @param ConnectionInterface $conn
	 */
	protected function addClient(ConnectionInterface $conn) {
		$client = new ChatClient($this, $conn, $this->getUserSupport());
		$this->connections->attach($conn, $client);
		$this->clients->attach($client);
	}

	/**
	 * When a client disconnects, this method removes them from the list
	 * @param ConnectionInterface $conn
	 */
	protected function removeClient(ConnectionInterface $conn) {
		$client = $this->resolveClient($conn);

		$this->connections->detach($conn);
		$this->clients->detach($client);
	}

	/**
	 * Finds the ChatClient object associated with a given ConnectionInterface
	 * @param ConnectionInterface $conn
	 * @return ChatClient The client for the given interface
	 */
	protected function resolveClient(ConnectionInterface $conn) {
		return $this->connections[$conn];
	}

	/**
	 * Finds a client in the server by name. Searches first by username, then by display name
	 * if no clients are found.
	 * @param string $name The name of the client to find
	 * @return ChatClient The client with that name
	 */
	public function findClient($name) {
		$name = strtolower($name);

		//Try to match by username first
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);
			//Don't let us perform stuff on hidden clients
			if (!$client->getVisible())
				continue;
			if (strtolower($client->getUsername()) === $name) {
				return $client;
			}
		}
		//If that fails, try by display name
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);
			//Don't let us perform stuff on hidden clients
			if (!$client->getVisible())
				continue;
			if (strtolower($client->getDisplayName()) === $name) {
				return $client;
			}
		}
		return null;
	}
	/**
	 * Send data to every client in the server, with an optional excluded client.
	 * @param                 $msg
	 * @param ChatClient|null $exclude
	 */
	public function broadcast($msg, ChatClient $exclude = null) {
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$client->send($msg);
		}
	}

	/**
	 * Execute a server command on every client in the server, with an optional excluded client.
	 * @param IServerCommand  $command
	 * @param ChatClient|null $exclude
	 */
	public function broadcastCommand(IServerCommand $command, ChatClient $exclude = null) {
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$command->execute($client);
		}
	}

	/**
	 * Update the user lists of all connected clients
	 */
	public function sendAllUserlists() {
		$command = new Server\UserlistCommand($this, $this->clients);
		$this->broadcastCommand($command);
	}

	/**
	 * Get all of the clients in the server
	 * @return \SplObjectStorage A storage of all the clients
	 */
	public function getAllClients() {
		return $this->clients;
	}

	/**
	 * Schedule a callback to be evaluated after a specific amount of time
	 * @param          $time  
	 * @param callable $callback
	 * @return TimerInterface
	 */
	public function schedule($time, callable $callback) {
		return $this->scheduler->addTimer($time, $callback);
	}

	/**
	 * Schedule a callback to be evaluated on a constant interval
	 * @param          $interval
	 * @param callable $callback
	 * @return TimerInterface
	 */
	public function scheduleLoop($interval, callable $callback) {
		return $this->scheduler->addPeriodicTimer($interval, $callback);
	}

	/**
	 * Get the server's ServerSupport
	 * @return IServerSupport The support
	 */
	public function getServerSupport() {
		return $this->serverSupport;
	}

	/**
	 * Get the server's UserSupport
	 * @return IUserSupport The support
	 */
	public function getUserSupport() {
		return $this->userSupport;
	}

	/**
	 * Callback method for when a client finishes logging in
	 * @param ChatClient $client The client who is logging in
	 * @return boolean If the client should be disconnected
	 */
	public function onClientLogin(ChatClient $client) {
		//Check if we've kicked this client and they're not allowed in
		if (in_array($client->getUsername(), $this->kickedClients)) {
			//They're kicked
			return false;
		}

		//Check if this client is banned
		if (in_array($client->getUsername(), $this->bannedClients)) {
			//They're banned
			//TODO: Shadowbanning
			return false;
		}

		$this->sendAllUserlists();
		$this->broadcastCommand(new Server\NotifyCommand($this, $client, "login", -1, $client->getLocation()), $client);
		return true;
	}

	/**
	 * Callback method for when a client logs out.
	 * @param ChatClient $client The client who is logging out
	 */
	public function onClientLogout(ChatClient $client) {

	}

	/**
	 * Disconnect a client from the server for a given length of time
	 * @param ChatClient $client The client to kick
	 * @param int        $time For how long the client is kicked
	 */
	public function kickClient(ChatClient $client, $time) {
		$username = $client->getUsername();
		$this->kickedClients[] = $username;
		$this->schedule($time, function() use($username) {
			//Find the position of the username
			$position = array_search($username, $this->kickedClients);
			//Splice the object out from the middle of the array
			array_splice($this->kickedClients, $position, 1);
		});
		$client->disconnect();
	}

	/**
	 * Ban a client, preventing them from joining for a number of days
	 * @param ChatClient $client The client to ban
	 * @param int        $days   How many days they are banned for, or -1 if indefinite
	 */
	public function banClient(ChatClient $client, $days) {
		//TODO: Shadowbanning
		$username = $client->getUsername();
		$this->bannedClients[] = $username;

		//If their ban isn't indefinite
		if ($days > 0) {
			$this->schedule($days * 86400, function () use ($username) {
				//Find the position of the username
				$position = array_search($username, $this->bannedClients);
				//Splice the object out from the middle of the array
				array_splice($this->bannedClients, $position, 1);
			});
		}
		$client->disconnect();
	}

	/**
	 * Check if a given version is allowed to join the server
	 * @param int $version The version number
	 * @return boolean If a client using that version can join
	 */
	public function checkVersion($version) {
		return $this->serverSupport->checkVersion($version);
	}
}
