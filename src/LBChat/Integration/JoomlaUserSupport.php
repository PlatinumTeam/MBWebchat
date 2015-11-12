<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define('JPATH_BASE', realpath("../public_html")); //Joomla root path
define('__DIR__', JPATH_BASE);

require_once(JPATH_BASE . DS . 'includes' . DS . 'defines.php');
require_once(JPATH_BASE . DS . 'includes' . DS . 'framework.php');

$mainframe = \JFactory::getApplication('site');
$mainframe->initialise();

jimport("joomla.user.authorization");
jimport("joomla.user.authentication");

restore_exception_handler();

class JoomlaUserSupport implements IUserSupport {
	const cacheTime = 10;
	/**
	 * @var Database $database
	 */
	protected $database;

	/**
	 * @var IUserSupport $backup
	 */
	protected $backup;

	protected $userCache;
	protected $idCache;

	public function __construct(Database $database, IUserSupport $backup = null) {
		$this->database = $database;
		$this->backup = $backup;
		$this->userCache = array();
		$this->idCache = array();
	}

	/**
	 * @param int $id The user's id
	 * @return \JUser The user object for this user
	 */
	protected function getUser($id) {
		//Cache the user results into an array because getUser takes forever
		if (array_key_exists($id, $this->userCache)) {
			$cached = $this->userCache[$id];
			//Cache timeout is stored in $cached[1]
			if (microtime(true) - $cached[1] > self::cacheTime) {
				//Update the user
				$user = \JFactory::getUser($id);
				$this->userCache[$id] = array($user, microtime(true));
			}
		} else {
			//Not cached yet- create a new one
			$user = \JFactory::getUser($id);
			$this->userCache[$id] = array($user, microtime(true));
		}

		//User object is stored in $cached[0]
		return $this->userCache[$id][0];
	}

	public function getId($username) {
		//Cache ids for usernames. This doesn't need a timeout because these don't (ever) change.
		// At least I hope so.
		if (!array_key_exists($username, $this->idCache)) {
			$this->idCache[$username] = \JUserHelper::getUserId($username);
		}
		return $this->idCache[$username];
	}

	public function getUsername($username) {
		return $this->getUser($this->getId($username))->username;
	}

	public function getAccess($username) {
		if ($this->backup !== null)
			return $this->backup->getAccess($username);
		return 0;
	}

	public function getDisplayName($username) {
		return self::getUser(self::getId($username))->name;
	}

	public function getColor($username) {
		$id = self::getId($username);
		$query = $this->database->prepare("SELECT `colorValue` FROM `bv2xj_users` WHERE `id` = :id");
		$query->bindParam(":id", $id);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public function getTitles($username) {
		$id = self::getId($username);
		$query = $this->database->prepare(
			"SELECT `title`, 'flair' FROM `bv2xj_user_titles` WHERE `id` = (SELECT `titleFlair` FROM `bv2xj_users` WHERE `id` = :uid)
				UNION
			SELECT `title`, 'prefix' FROM `bv2xj_user_titles` WHERE `id` = (SELECT `titlePrefix` FROM `bv2xj_users` WHERE `id` = :uid)
				UNION
			SELECT `title`, 'suffix' FROM `bv2xj_user_titles` WHERE `id` = (SELECT `titleSuffix` FROM `bv2xj_users` WHERE `id` = :uid)");
		$query->bindParam(":uid", $id);
		$query->execute();

		if (!$query->rowCount())
			return array("", "", "");

		$rows = $query->fetchAll();

		return array(
			@$rows[0]["title"],
			@$rows[1]["title"],
			@$rows[2]["title"]
		);
	}

	/**
	 * Attempt to login a user.
	 * @param string $username The user's username
	 * @param string $type Either "key" or "password" for which method to use
	 * @param string $data The key/password to use
	 * @return boolean If the login succeeded
	 */
	public function tryLogin($username, $type, $data) {
		if ($type === "guest") {
			//Guests get access automatically
			return true;
		}
		if ($type === "key") {
			if ($this->backup !== null) {
				return $this->backup->tryLogin($username, $type, $data);
			}
			//Cannot currently handle key-based logins
			return false;
		}
		if ($type === "password") {
			$user = $this->getUser($username);

			if ($user->id === 0) {
				//User does not exist
				return false;
			}

			if ($user->guest) {
				//Cannot login guests
				return false;
			}

			if ($user->block) {
				if ($user->activation !== "") {
					//You're not activated

					//TODO: Automatically activate them
					return true;
				} else {
					//You're banned
					return false;
				}
			}
			if (!$this->checkPassword($username, $data)) {
				//Invalid password
				return false;
			}
			return true;
		}
		//Unsupported login type
		return false;
	}

	/**
	 * Check if a user provided the correct password
	 * @param string $username The user's username
	 * @param string $password The user's password
	 * @return boolean If their password matches
	 */
	protected function checkPassword($username, $password) {
		$credentials = array("username" => $username, "password" => $password);
		$options = array("remember" => false, "silent" => false);

		//Get the global JAuthentication object.
		$authenticate = \JAuthentication::getInstance();
		$response = $authenticate->authenticate($credentials, $options);

		return ($response->status === \JAuthentication::STATUS_SUCCESS);
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