<?php
namespace LBChat;
use LBChat\Command\Server;
use LBChat\Command\Server\IServerCommand;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {
		$client = new ChatClient($this, $conn);
		$this->clients->attach($conn, $client);
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

		$this->clients->detach($conn);
	}
	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "An error has occurred: {$e->getMessage()}\n";

		$conn->close();
	}

	/**
	 * @param ConnectionInterface $conn
	 * @return ChatClient
	 */
	protected function resolveClient(ConnectionInterface $conn) {
		return $this->clients[$conn];
	}
	/**
	 * @param                 $msg
	 * @param ChatClient|null $exclude
	 */
	public function broadcast($msg, ChatClient $exclude = null) {
		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$client->send($msg);
		}
	}

	public function broadcastCommand(IServerCommand $command, ChatClient $exclude = null) {
		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$command->execute($client);
		}
	}

	public function notify(ChatClient $sender, $type, $access = 0, $message = "") {
		//Notify all clients
		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);
			if ($client->getAccess() >= $access)
				$client->notify($sender, $type, $access, $message);
		}
	}

	public function sendUserlist(ChatClient $recipient) {
		//Send them a userlist
		$command = new Server\UserlistCommand($this);
		$command->start($recipient);

		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);
			$command->send($recipient, $client);
		}

		$command->done($recipient);
	}

	public function sendAllUserlists() {
		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);
			$this->sendUserlist($client);
		}
	}
}
