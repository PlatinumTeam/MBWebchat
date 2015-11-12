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

	/**
	 * Attempt to login a user.
	 * @param string $username The user's username
	 * @param string $type Either "key" or "password" for which method to use
	 * @param string $data The key/password to use
	 * @return boolean If the login succeeded
	 */
	public function tryLogin($username, $type, $data) {
		if ($type === "key") {
			$query = $this->database->prepare("SELECT * FROM `users` WHERE `username` = :username");
			$query->bindParam(":username", $username);
			$query->execute();
			$userInfo = $query->fetch(\PDO::FETCH_ASSOC);

			//Don't let people on if they've been banned
			if ($userInfo["banned"]) {
				//TODO: Shadowbanning
				return false;
			}

			//Make sure their key matches
			return $userInfo["chatkey"] === $data;
		}
		//Cannot handle other types of logins
		return false;
	}

	/**
	 * Get a temporary username for a guest
	 * @return string The guest's username
	 */
	public function getGuestUsername() {
		//Generate a random username
		$username = "Guest_" . substr(md5(time()), 0, 8);
		return $username;
	}

	/**
	 * Determine if a user is a guest by their username
	 * @param string $username The username to check
	 * @return boolean If they're a guest
	 */
	public function isGuest($username) {
		return (stristr($username, "Guest_") !== false);
	}
}