<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Chat\IChatCommand;

abstract class ChatCommandFactory {
	static $commandTypes = array();
	/**
	 * Construct a client command object from a given message, that can be executed.
	 * @return IChatCommand
	 */
	public static function construct(ChatClient $client, ChatServer $server, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		$first = strtolower($first); //Case-insensitive comparison
		$first = substr($first, 1); //Strip off the / at the start of the command

		if (array_key_exists($first, self::$commandTypes)) {
			$constructor = self::$commandTypes[$first];
			return call_user_func($constructor, $client, $server, $rest);
		} else {
			return null;
		}
	}

	public static function addCommandType($name, $constructor) {
		self::$commandTypes[strtolower($name)] = $constructor;
	}

	public static function init() {
		require "Chat/Init.php";
	}
}