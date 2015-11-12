<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use LBChat\Command\Server\AcceptTOSCommand;
use LBChat\Command\Server\InfoCommand;
use LBChat\Integration\IUserSupport;
use Ratchet\ConnectionInterface;

class SQLChatClient extends ChatClient {
	protected $databases;
	protected $support;

	public function __construct(SQLChatServer $server, ConnectionInterface $connection, array $databases, IUserSupport $support) {
		parent::__construct($server, $connection);
		$this->databases = $databases;
		$this->support = $support;
	}

	public function onLogin() {
		//Check if they need to accept the TOS
		$query = $this->db("platinum")->prepare("SELECT `acceptedTos` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();
		if (!$query->fetchColumn(0)) {
			//They do need to accept the TOS
			$command = new AcceptTOSCommand($this->server);
			$command->execute($this);
			return;
		}

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

		$this->server->sendAllUserlists();
	}

	public function onLogout() {
		parent::onLogout();

		$query = $this->db("platinum")->prepare("DELETE FROM `loggedin` WHERE `username` = :username LIMIT 1");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();
	}

	public function getId() {
		return $this->support->getId($this->getUsername());
	}

	public function getUsername() {
		return $this->support->getUsername(parent::getUsername());
	}

	public function getDisplayName() {
		return $this->support->getDisplayName($this->getUsername());
	}

	public function getAccess() {
		return $this->support->getAccess($this->getUsername());
	}

	public function getColor() {
		return $this->support->getColor($this->getUsername());
	}

	public function getTitles() {
		return $this->support->getTitles($this->getUsername());
	}

	public function tryLogin($type, $data) {
		return $this->support->tryLogin($this->getUsername(), $type, $data);
	}

	public function setGuest() {
		parent::setGuest();
		//Get us an actual guest username
		$this->setUsername($this->support->getGuestUsername());
	}

	/**
	 * @param $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}
}