<?php
namespace LBChat;
use LBChat\Command\Server;
use LBChat\Command\Server\IServerCommand;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
	protected $connections;
	protected $clients;

	public function __construct() {
		$this->connections = new \SplObjectStorage();
		$this->clients = new \SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->addClient($conn);
	}
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
	public function onClose(ConnectionInterface $conn) {
		$client = $this->resolveClient($conn);
		$client->onLogout();

		$this->removeClient($conn);
	}
	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}

	protected function addClient(ConnectionInterface $conn) {
		$client = new ChatClient($this, $conn);
		$this->connections->attach($conn, $client);
		$this->clients->attach($client);
	}

	protected function removeClient(ConnectionInterface $conn) {
		$client = $this->resolveClient($conn);

		$this->connections->detach($conn);
		$this->clients->detach($client);
	}

	/**
	 * @param ConnectionInterface $conn
	 * @return ChatClient
	 */
	protected function resolveClient(ConnectionInterface $conn) {
		return $this->connections[$conn];
	}
	/**
	 * @param                 $msg
	 * @param ChatClient|null $exclude
	 */
	public function broadcast($msg, ChatClient $exclude = null) {
		foreach ($this->clients as $client) {
			if ($exclude !== null && $client->compare($exclude))
				continue;

			$client->send($msg);
		}
	}

	public function broadcastCommand(IServerCommand $command, ChatClient $exclude = null) {
		foreach ($this->clients as $client) {
			if ($exclude !== null && $client->compare($exclude))
				continue;

			$command->execute($client);
		}
	}

	public function notify(ChatClient $sender, $type, $access = 0, $message = "") {
		//Notify all clients
		foreach ($this->clients as $client) {
			if ($client->getAccess() >= $access)
				$client->notify($sender, $type, $access, $message);
		}
	}

	public function sendUserlist(ChatClient $recipient) {
		//Send them a userlist
		$command = new Server\UserlistCommand($this, $this->clients);
		$command->execute($recipient);
	}

	public function sendAllUserlists() {
		$command = new Server\UserlistCommand($this, $this->clients);
		foreach ($this->clients as $client) {
			$command->execute($client);
		}
	}
}
