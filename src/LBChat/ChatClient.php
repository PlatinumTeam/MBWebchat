<?php
namespace LBChat;
use LBChat\Command\Server\NotifyCommand;
use Ratchet\ConnectionInterface;

class ChatClient {
	protected $server;
	protected $connection;
	private $username;
	private $display;
	private $location;
	private $access;
	private $color;
	private $titles;

	public function __construct(ChatServer $server, ConnectionInterface $connection) {
		$this->server = $server;
		$this->connection = $connection;
		$this->location = 0;
		$this->access = 0;
		$this->color = "000000";
		$this->titles = array("", "", "");
	}

	public function interpretMessage($msg) {
		$command = Command\CommandFactory::construct($this, $this->server, $msg);

		if ($command === null) {
			//TODO: Send commands
			$this->send("INVALID");
		} else {
			$command->execute();
		}
	}

	public function send($msg) {
		$this->connection->send($msg);
	}

	public function onLogin() {
		//TODO: Send commands
		$this->send("LOGGED");
		$this->server->sendAllUserlists();
		$this->server->broadcastCommand(new NotifyCommand($this->server, $this, "login", -1, $this->location), $this);
	}

	public function onLogout() {
		$this->server->sendAllUserlists();
		$this->server->broadcastCommand(new NotifyCommand($this->server, $this, "logout", -1, $this->location), $this);
	}

	public function compare(ChatClient $other) {
		return $other->connection === $this->connection;
	}

	public function getId() {
		return $this->connection->resourceId;
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

	public function getPrivilege() {
		switch ($this->getAccess()) {
		case -3: return 0;
		case 3:  return 0;
		default: return $this->getAccess();
		}
	}

	public function getColor() {
		return $this->color;
	}

	public function setColor($color) {
		$this->color = $color;
	}

	public function getTitles() {
		return $this->titles;
	}

	public function setTitles($titles) {
		$this->titles = $titles;
	}

	public function setTitle($index, $title) {
		$this->titles[$index] = $title;
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
