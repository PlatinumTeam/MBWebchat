<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use LBChat\Command\Server\InfoCommand;
use LBChat\Integration\JoomlaUserSupport;
use LBChat\Integration\LBUserSupport;
use Ratchet\ConnectionInterface;

class SQLChatClient extends ChatClient {
	protected $databases;

	public function __construct(SQLChatServer $server, ConnectionInterface $connection, array $databases) {
		parent::__construct($server, $connection);
		$this->databases = $databases;
	}

	public function onLogin() {
		$command = new InfoCommand($this->server);
		$command->execute($this);

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
		return JoomlaUserSupport::getId($this->getUsername());
	}

	public function getUsername() {
		return LBUserSupport::getUsername(parent::getUsername());
	}

	public function getDisplayName() {
		return JoomlaUserSupport::getDisplayName($this->getUsername());
	}

	public function getAccess() {
		return LBUserSupport::getAccess($this->getUsername());
	}

	public function getColor() {
		return JoomlaUserSupport::getColor($this->getUsername());
	}

	public function getTitles() {
		return JoomlaUserSupport::getTitles($this->getUsername());
	}

	/**
	 * @param $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}
}