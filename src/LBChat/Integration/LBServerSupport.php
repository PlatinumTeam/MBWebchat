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

	public static function getWelcomeMessage() {
		return self::getPreference("welcome") . "\\n\\nTODO: Qotd";
	}
}