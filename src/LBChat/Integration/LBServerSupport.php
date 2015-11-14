<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

abstract class LBServerSupport {
	/**
	 * @var Database $database
	 */
	protected static $database;

	public static function setDatabase(Database $database) {
		self::$database = $database;
	}

	public static function getPreference($key) {
		$query = self::$database->prepare("SELECT `value` FROM `settings` WHERE `key` = :key");
		$query->bindParam(":key", $key);
		$query->execute();

		if (!$query->rowCount())
			return "";

		return $query->fetchColumn(0);
	}

	public static function getStatusList() {
		$query = self::$database->prepare("SELECT `status`, `display` FROM `statuses`");
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getColorList() {
		$query = self::$database->prepare("SELECT `ident`, `color` FROM `chatcolors`");
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_ASSOC);
	}

	public static function getWelcomeMessage($webchat = false) {
		$message = self::getPreference($webchat ? "webwelcome" : "welcome");

		//Get qotd from the database
		$query = self::$database->prepare("SELECT * FROM `qotd` WHERE `selected` = 1");
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
}