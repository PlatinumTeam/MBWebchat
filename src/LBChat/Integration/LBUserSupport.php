<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

class LBUserSupport implements IUserSupport {
	/**
	 * @var Database $database
	 */
	protected $database;

	public function __construct(Database $database) {
		$this->database = $database;
	}

	public function getUsername($username) {
		$query = $this->database->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getAccess($username) {
		$query = $this->database->prepare("SELECT `access` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getId($username) {
		$query = $this->database->prepare("SELECT `id` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getDisplayName($username) {
		$query = $this->database->prepare("SELECT `display` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getColor($username) {
		return "000000";
	}

	public function getTitles($username) {
		return array("", "", "");
	}
}