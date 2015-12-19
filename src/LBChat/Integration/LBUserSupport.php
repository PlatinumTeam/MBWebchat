<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

class LBUserSupport implements IUserSupport {
	/**
	 * @var Database $database
	 */
	protected $database;

	/**
	 * @var array $userCache
	 */
	protected $userCache;

	public function __construct(Database $database) {
		$this->database = $database;
		$this->userCache = [];
	}

	public function getUsername($username) {
		//Workaround: Guests are never in the user database
		if ($username === "Guest")
			return $username;

		//Check the cache, don't go through the DB if we don't have to
		if (array_key_exists($username, $this->userCache)) {
			return $this->userCache[$username];
		}

		$query = $this->database->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();

		//This person has no username. Strange
		if ($query->rowCount() === 0)
			return $username;

		//Cache their username
		$this->userCache[$username] = $query->fetchColumn(0);
		return $this->userCache[$username];
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

		// Add them to the database
		$query = $this->database->prepare("INSERT INTO `users` (`display`, `username`, `pass`, `salt`, `email`, `showemail`, `secretq`, `secreta`, `rating_mp`, `guest`) VALUES (:display, :username, 'guest', 'guest', '', 0, '', '', -1, 1)");
		$query->bindParam(":display", $username);
		$query->bindParam(":username", $username);
		$query->execute();

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

	/**
	 * Check if a user is banned from the site
	 * @param string $username The username to check
	 * @param string $address  The user's IP address
	 * @return boolean If that user is banned
	 */
	public function isBanned($username, $address) {
		//Check for IP bans
		$query = $this->database->prepare("SELECT * FROM `bannedips` WHERE `address` = :address");
		$query->bindParam(":address", $address);
		$query->execute();

		if ($query->rowCount() > 0) {
			//Wow, someone actually got IP-banned. What are the chances.
			return true;
		}

		//Guests don't have usernames, and can't be username-banned
		if ($username === "")
			return false;

		//Check for username bans
		$query = $this->database->prepare("SELECT `banned` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();

		//Make sure they exist.
		if ($query) {
			//Whatever the database says
			return $query->fetchColumn(0);
		}

		//No user? Not sure if this is ever called
		return false;
	}

	/**
	 * Add a friend for a user
	 * @param string $username The user's username
	 * @param string $friend   The friend's username
	 */
	public function addFriend($username, $friend) {
		//This database was designed by a moron.
		//TODO: Make friends not by id, or everything by id
		$friendId = $this->getId($friend);

		//Check if they have this friend already
		$query = $this->database->prepare("SELECT * FROM `friends` WHERE `username` = :username AND `friendid` = :friendid");
		$query->bindParam(":username", $username);
		$query->bindParam(":friendId", $friendId);
		$query->execute();

		//Already have this friend, don't add them twice
		if ($query->rowCount()) {
			return;
		}

		//Don't have them, add them
		$query = $this->database->prepare("INSERT INTO `friends` (`username`, `friendid`) VALUES (:username, :friendId)");
		$query->bindParam(":username", $username);
		$query->bindParam(":friendId", $friendId);
		$query->execute();
	}

	/**
	 * Add a user's friend
	 * @param string $username The user's username
	 * @param string $friend   The friend's username
	 */
	public function removeFriend($username, $friend) {
		//This database was designed by a moron.
		//TODO: Make friends not by id, or everything by id
		$friendId = $this->getId($friend);

		//Check if they have this friend already
		$query = $this->database->prepare("SELECT * FROM `friends` WHERE `username` = :username AND `friendid` = :friendid");
		$query->bindParam(":username", $username);
		$query->bindParam(":friendId", $friendId);
		$query->execute();

		//Don't have this friend, can't remove them
		if (!$query->rowCount()) {
			return;
		}

		//Remove this friend from our list
		$query = $this->database->prepare("DELETE FROM `friends` WHERE `username` = :username AND `friendid` = :friendid");
		$query->bindParam(":username", $username);
		$query->bindParam(":friendId", $friendId);
		$query->execute();
	}

	/**
	 * Get a user's friend list
	 * @param string $username The user's username
	 * @return array The user's friend list
	 */
	public function getFriendList($username) {
		//This database was designed by a moron.
		//TODO: Make friends not by id, or everything by id
		$query = $this->database->prepare("SELECT `username` FROM `users` WHERE `id` IN (SELECT `friendid` FROM `friends` WHERE `username` = :username)");
		$query->bindParam(":username", $username);
		$query->execute();
		$list = $query->fetchAll(\PDO::FETCH_ASSOC);

		//Convert this list of usernames into username / display pairs
		$list = array_map(function ($friend) {
			return array("username" => $friend["username"], "display" => $this->getDisplayName($friend["username"]));
		}, $list);
		return $list;
	}
}