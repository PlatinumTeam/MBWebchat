<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use Ratchet\ConnectionInterface;

class SQLChatClient extends ChatClient {
	protected $databases;

	public function __construct(SQLChatServer $server, ConnectionInterface $connection, array $databases) {
		parent::__construct($server, $connection);
		$this->databases = $databases;
	}

	public function onLogin() {
		parent::onLogin();

		$query = $this->db("platinum")->prepare("INSERT INTO `loggedin` SET
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

		$query = $this->db("platinum")->prepare("DELETE FROM `loggedin` WHERE `username` = :username LIMIT 1");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();
	}

	public function getId() {
		$username = $this->getUsername();
		$query = $this->db("joomla")->prepare("SELECT `id` FROM `bv2xj_users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getUsername() {
		$username = parent::getUsername();
		$query = $this->db("platinum")->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getDisplayName() {
		$username = parent::getUsername();
		$query = $this->db("joomla")->prepare("SELECT `name` FROM `bv2xj_users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	/**
	 * @param $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}
}