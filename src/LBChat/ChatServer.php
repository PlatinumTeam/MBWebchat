<?php
namespace LBChat;
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

	public function notify(ChatClient $sender, $type, $access = 0, $message = "") {
		//Notify all clients
		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);
			if ($client->getAccess() >= $access)
				$client->notify($sender, $type, $access, $message);
		}
	}

	public function sendUserlist(ChatClient $client) {
		//Send them a userlist
		$client->send("USER START");

		foreach ($this->clients as $conn) {
			$client = $this->resolveClient($conn);

			$username = $client->getUsername();
			$display = $client->getDisplayName();
			$access = $client->getAccess();
			$location = $client->getLocation();

			$color = "000000"; //TODO: User colors
			//TODO: User titles
			$flair = "";
			$prefix = "";
			$suffix = "";

			$client->send("USER COLORS $username $color $color $color\n");
			$client->send("USER TITLES $flair $prefix $suffix\n");
			$client->send("USER NAME $username $access $location $display\n");
		}

		$client->send("USER DONE");
	}
}
