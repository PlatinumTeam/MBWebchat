<?php
namespace LBChat;
use Ratchet\ConnectionInterface;

class ChatClient {
	private $connection;
	private $username;

	public function __construct(ConnectionInterface $connection) {
		$this->connection = $connection;
	}

	public function interpretMessage($msg) {
		$command = Command\CommandFactory::construct($this, $msg);
		$command->parse();
	}

	public function send($msg) {
		$this->connection->send($msg);
	}

	public function onLogin($location) {

	}

	public function onLogout() {

	}

	public function compare(ChatClient $other) {
		return $other->connection === $this->connection;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
	}
}
