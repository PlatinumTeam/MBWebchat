<?php
namespace LBChat\Integration;

abstract class LBUserSupport {
	/**
	 * @var \PDO $database
	 */
	protected static $database;

	public static function setDatabase(\PDO $database) {
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