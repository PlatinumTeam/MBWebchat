<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

abstract class LBUserSupport {
	/**
	 * @var Database $database
	 */
	protected static $database;

	public static function setDatabase(Database $database) {
		self::$database = $database;
	}

	public static function getUsername($username) {
		$query = self::$database->prepare("SELECT `username` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public static function getAccess($username) {
		$query = self::$database->prepare("SELECT `access` FROM `users` WHERE `username` = :username");
		$query->bindParam(":username", $username);
		$query->execute();
		return $query->fetchColumn(0);
	}
}