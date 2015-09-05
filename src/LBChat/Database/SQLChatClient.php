<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use Ratchet\ConnectionInterface;

class SQLChatClient extends ChatClient {
	protected $database;

	public function __construct(SQLChatServer $server, ConnectionInterface $connection, Database $database) {
		parent::__construct($server, $connection);
		$this->database = $database;
	}

	public function onLogin() {
		parent::onLogin();

		$query = $this->database->prepare("INSERT INTO `loggedin` SET
				`username` = :username,
				`display` = :display,
				`access` = :access,
				`location` = :location
			"
		);
		$query->bindParam(":username", $this->getUsername());
		$query->bindParam(":display", $this->getDisplayName());
		$query->bindParam(":access", $this->getAccess());
		$query->bindParam(":location", $this->getLocation());
		$query->execute();
	}

	public function onLogout() {
		parent::onLogout();

		$query = $this->database->prepare("DELETE FROM `loggedin` WHERE `username` = :username LIMIT 1");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();
	}

	public function getUsername() {
		$username = parent::getUsername();
		$query = $this->database->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getDisplayName() {
		$username = parent::getUsername();
		$query = $this->database->prepare("SELECT `display` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}
}