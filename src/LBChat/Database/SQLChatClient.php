<?php
namespace LBChat\Database;

use LBChat\ChatClient;
use LBChat\Command\Server\AcceptTOSCommand;
use LBChat\Command\Server\IdentifyCommand;
use LBChat\Command\Server\InfoCommand;
use LBChat\Integration\IUserSupport;
use Ratchet\ConnectionInterface;

class SQLChatClient extends ChatClient {
	protected $databases;

	public function __construct(SQLChatServer $server, ConnectionInterface $connection, array $databases, IUserSupport $support) {
		parent::__construct($server, $connection, $support);
		$this->databases = $databases;
	}

	public function onLogin() {
		if (!parent::onLogin())
			return false;

		$command = new InfoCommand($this->server);
		$command->execute($this);

		$query = $this->db("platinum")->prepare("INSERT INTO `loggedin` SET
			`username` = :username,
			`display` = :display,
			`access` = :access,
			`location` = :location
		");
		$query->bindParam(":username", $this->getUsername());
		$query->bindParam(":display", $this->getDisplayName());
		$query->bindParam(":access", $this->getAccess());
		$query->bindParam(":location", $this->getLocation());
		$query->execute();

		$this->server->sendAllUserlists();

		return true;
	}

	public function onLogout() {
		parent::onLogout();

		$query = $this->db("platinum")->prepare("DELETE FROM `loggedin` WHERE `username` = :username LIMIT 1");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();

		//If we're a guest, we should clean up ourselves.
		if ($this->isGuest()) {
			$query = $this->db("platinum")->prepare("DELETE FROM `users` WHERE `username` = :username LIMIT 1");
			$query->bindParam(":username", $this->getUsername());
			$query->execute();
		}
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

	public function getAcceptedTOS() {
		//Check if they need to accept the TOS
		$query = $this->db("platinum")->prepare("SELECT `acceptedTos` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $this->getUsername());
		$query->execute();
		return !$query->fetchColumn(0);
	}

	public function setAcceptedTOS($accepted) {
		$query = $this->db("platinum")->prepare("UPDATE `users` SET `acceptedTos` = :accepted WHERE `username` = :username");
		$query->bindParam(":username", $this->getUsername());
		$query->bindParam(":accepted", $accepted);
		$query->execute();
	}

	public function addFriend($friend) {
		$this->support->addFriend($this->getUsername(), $friend);
	}

	public function removeFriend($friend) {
		$this->support->removeFriend($this->getUsername(), $friend);
	}

	public function getFriendList() {
		return $this->support->getFriendList($this->getUsername());
	}
	/**
	 * @param $name
	 * @return Database
	 */
	protected function db($name) {
		return $this->databases[$name];
	}
}