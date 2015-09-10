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
	public static function construct(ChatServer $server, ChatClient $client, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		//Try to find a matching command
		$lower = strtolower($msg);

		foreach (self::$commandTypes as $name => $options) {
			/* @var callable $constructor */
			list($constructor, $caseSensitive) = $options;

			//Some may be case-sensitive
			$start = strpos(($caseSensitive ? $msg : $lower), $name);

			//If the command was found at the start of the string
			if ($start === 0) {
				//Strip off the starting portion of the command
				$rest = ltrim(substr($msg, strlen($name)));

				//And try to call the constructor of the command type.
				$result = call_user_func($constructor, $server, $client, $rest, $msg);

				//If we don't succeed, keep trying commands until we do.
				if ($result !== null)
					return $result;
			}
		}
		//No command worked
		return null;
	}

	public static function addCommandType($name, callable $constructor, $caseSensitive = false) {
		if (!$caseSensitive)
			$name = strtolower($name);

		self::$commandTypes[$name] = array($constructor, $caseSensitive);
	}

	public static function init() {
		require "Chat/Init.php";
	}
}