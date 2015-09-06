<?php
namespace LBChat;
use LBChat\Command\Server;
use LBChat\Command\Server\IServerCommand;
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

	/**
	 * @var LoopInterface $scheduler
	 */
	protected $scheduler;

	public function __construct() {
		$this->connections = new \SplObjectStorage();
		$this->clients = new \SplObjectStorage();

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
		$client = new ChatClient($this, $conn);
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
	 * @return ChatClient
	 */
	protected function resolveClient(ConnectionInterface $conn) {
		return $this->connections[$conn];
	}

	/**
	 * Finds a client in the server by name. Searches first by username, then by display name
	 * if no clients are found.
	 * @param $name
	 * @return ChatClient
	 */
	public function findClient($name) {
		//Try to match by username first
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);
			if ($client->getUsername() === $name) {
				return $client;
			}
		}
		//If that fails, try by display name
		foreach ($this->connections as $conn) {
			$client = $this->resolveClient($conn);
			if ($client->getDisplayName() === $name) {
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
}
