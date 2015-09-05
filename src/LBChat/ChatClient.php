<?php
namespace LBChat;
use Ratchet\ConnectionInterface;

class ChatClient {
	private $server;
	private $connection;
	private $username;
	private $display;
	private $location;
	private $access;

	public function __construct(ChatServer $server, ConnectionInterface $connection) {
		$this->server = $server;
		$this->connection = $connection;
		$this->location = 0;
		$this->access = 0;
	}

	public function interpretMessage($msg) {
		$command = Command\CommandFactory::construct($this, $this->server, $msg);

		if ($command === null) {
			//TODO: Send commands
			$this->send("INVALID");
		} else {
			$command->parse();
		}
	}

	public function send($msg) {
		$this->connection->send($msg);
	}

	public function notify(ChatClient $sender, $type, $access = 0, $message = "") {
		$username = $sender->getUsername();
		$display = $sender->getDisplayName();

		//TODO: Send commands
		$this->send("NOTIFY $type $access $username $display $message");
	}

	public function onLogin() {
		//TODO: Send commands
		$this->send("LOGGED");

		$this->server->notify($this, "login", -1, $this->location);
	}

	public function onLogout() {
		$this->server->notify($this, "logout", -1);
	}

	public function compare(ChatClient $other) {
		return $other->connection === $this->connection;
	}

	public function getUsername() {
		return $this->username;
	}

	public function setUsername($username) {
		$this->username = $username;
		$this->display = $username;
	}

	public function getDisplayName() {
		return $this->display;
	}

	public function setDisplayName($display) {
		$this->display = $display;
	}

	public function getLocation() {
		return $this->location;
	}

	public function setLocation($location) {
		$this->location = $location;
	}

	public function getAccess() {
		return $this->access;
	}

	public function setAccess($access) {
		$this->access = $access;
	}

	public function login($type, $data) {
		$status = false;
		switch ($type) {
		case "key":
			$status = Login\Helper::tryKey($this->getUsername(), $data);
			//Usually this is webchat
			$this->location = 3;
			break;
		case "password":
			$status = Login\Helper::tryKey($this->getUsername(), $data);
			//Usually this is in-game
			$this->location = 0;
			break;
		}

		if ($status === false) {
			//Login failed
		} else {
			//Login succeeded
			$this->onLogin();
		}
	}
}
