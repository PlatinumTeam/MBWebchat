<?php
namespace LBChat\Integration;

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

abstract class JoomlaUserSupport {
	/**
	 * @var \PDO $database
	 */
	protected static $database;

	public static function setDatabase(\PDO $database) {
		self::$database = $database;
	}

	protected static function getUser($id) {
		return \JFactory::getUser($id);
	}

	public static function getId($username) {
		return \JUserHelper::getUserId($username);
	}

	public static function getDisplayName($username) {
		return self::getUser(self::getId($username))->name;
	}

	public static function getColor($username) {
		$id = self::getId($username);
		$query = self::$database->prepare("SELECT `colorValue` FROM `bv2xj_users` WHERE `id` = :id");
		$query->bindParam(":id", $id);
		$query->execute();
		return $query->fetchColumn(0);
	}

	public static function getTitles($username) {
		$id = self::getId($username);
		$query = self::$database->prepare(
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
}