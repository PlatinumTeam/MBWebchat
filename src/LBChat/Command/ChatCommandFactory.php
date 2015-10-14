<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Chat\IChatCommand;

abstract class ChatCommandFactory {
	static $commandTypes = array();
	/**
	 * Construct a chat command object from a given message, that can be executed.
	 * @param ChatServer $server
	 * @param ChatClient $client
	 * @param string $msg
	 * @return IChatCommand
	 */
	public static function construct(ChatServer $server, ChatClient $client, $msg) {
		if ($msg === "")
			return null;

		//Try to find a matching command
		$lower = strtolower($msg);

		//Store this because it may be slow
		$access = $client->getAccess();

		foreach (self::$commandTypes as $name => $options) {
			/* @var callable $constructor */
			list($constructor, $requiredAccess, $caseSensitive) = $options;

			//Don't let clients use commands that they're not authorized to use
			if ($access < $requiredAccess)
				continue;

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

	/**
	 * Add a command to the factory's list of acceptable commands.
	 * @param string   $name           The name of the command for which messages will be tested
	 * @param callable $constructor    A constructor for creating a command object
	 * @param int      $requiredAccess The required access level to use the command
	 * @param boolean  $caseSensitive  If the command should be considered case-sensitive
	 */
	public static function addCommandType($name, callable $constructor, $requiredAccess, $caseSensitive = false) {
		if (!$caseSensitive)
			$name = strtolower($name);

		self::$commandTypes[$name] = array($constructor, $requiredAccess, $caseSensitive);
	}

	/**
	 * Initialize the command backend, loading all known commands from Init.php
	 */
	public static function init() {
		require "Chat/Init.php";
	}
}