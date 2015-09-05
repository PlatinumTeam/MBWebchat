<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Client\IClientCommand;
use LBChat\Command\Client\IdentifyCommand;

abstract class CommandFactory {
	static $commandTypes = array();
	/**
	 * Construct a client command object from a given message, that can be executed.
	 * @return IClientCommand
	 */
	public static function construct(ChatClient $client, ChatServer $server, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		if (array_key_exists($first, self::$commandTypes)) {
			$constructor = self::$commandTypes[$first];
			return call_user_func($constructor, $client, $server, $rest);
		} else {
			return null;
		}
	}

	public static function addCommandType($name, $class) {
		self::$commandTypes[strtoupper($name)] = $class;
	}

	public static function init() {
		require "Client/Init.php";
	}
}