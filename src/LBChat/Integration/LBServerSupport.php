<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

class LBServerSupport implements IServerSupport {
	/**
	 * @var Database $database
	 */
	protected $database;

	public function __construct(Database $database) {
		$this->database = $database;
	}

	public function getPreference($key) {
		$query = $this->database->prepare("SELECT `value` FROM `settings` WHERE `key` = :key");
		$query->bindParam(":key", $key);
		$query->execute();

		if (!$query->rowCount())
			return "";

		return $query->fetchColumn(0);
	}

	public function getStatusList() {
		$query = $this->database->prepare("SELECT `status`, `display` FROM `statuses`");
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getColorList() {
		$query = $this->database->prepare("SELECT `ident`, `color` FROM `chatcolors`");
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getWelcomeMessage($webchat = false) {
		$message = $this->getPreference($webchat ? "webwelcome" : "welcome");

		//Get qotd from the database
		$query = $this->database->prepare("SELECT * FROM `qotd` WHERE `selected` = 1");
		$query->execute();

		//We may have more than one, pluralize the name if we do
		if ($query->rowCount() == 1) {
			$message .= "\n\nLeaderboards' Quote of the Day: ";
		} else if ($query->rowCount() > 1) {
			$message.= "\n\nLeaderboards' Quotes of the Day:";
		}

		$rows = $query->fetchAll(\PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			$text = $row["text"];
			$user = $row["username"];
			$time = $row["timestamp"];

			//Get current year
			$year = (new \DateTime($time))->format("Y");

			//"quote" -Name Year
			$message .= "\n\"$text\" -$user $year";
		}
		//Escape newlines so we don't send this all as multiple messages
		$message = str_replace("\n", "\\n", $message);

		return $message;
	}

	/**
	 * Check if a given version is allowed to join the server
	 * @param int $version The version number
	 * @return boolean If a client using that version can join
	 */
	public function checkVersion($version) {
		$query = $this->database->prepare("SELECT `version` FROM `versions` ORDER BY `id` DESC LIMIT 1");
		$query->execute();

		$serverVersion = $query->fetchColumn(0);
		return ($version >= $serverVersion);
	}

	public function setQotd($sender, $message) {
		//HiGuy: Deactivate the old qotd
		$query = $this->database->prepare("UPDATE `qotd` SET `selected` = 0");
		$query->execute();

		//HiGuy: Add the new qotd
		$query = $this->database->prepare("INSERT INTO `qotd` (`text`, `username`, `selected`) VALUES (:text, :username, 1)");
		$query->bindParam(":text", $message);
		$query->bindParam(":username", $sender);
		$query->execute();
	}
}