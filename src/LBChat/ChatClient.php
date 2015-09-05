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

		if ($command === null) {
			//Error
		} else {
			$command->parse();
		}
	}

	public function send($msg) {
		$this->connection->send($msg);
	}

	public function onLogin() {
		$this->send("LOGGED\n");
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

	public function login($type, $data) {
		$status = false;
		switch ($type) {
		case "key": $status = Login\Helper::tryKey($this->getUsername(), $data); break;
		case "password": $status = Login\Helper::tryKey($this->getUsername(), $data); break;
		}

		if ($status === false) {
			//Login failed
		} else {
			//Login succeeded
			$this->onLogin();
		}
	}
}
