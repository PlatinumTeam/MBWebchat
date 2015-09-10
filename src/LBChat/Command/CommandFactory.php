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
	 * @param ChatServer $server
	 * @param ChatClient $client
	 * @param string $msg
	 * @return IClientCommand
	 */
	public static function construct(ChatServer $server, ChatClient $client, $msg) {
		$words = explode(" ", $msg);
		if ($msg === "" || count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		if (array_key_exists($first, self::$commandTypes)) {
			$constructor = self::$commandTypes[$first];
			return call_user_func($constructor, $server, $client, $rest);
		} else {
			return null;
		}
	}

	/**
	 * Add a command to the factory's list of acceptable commands.
	 * @param string   $name        The name of the command that will be sent
	 * @param callable $constructor A constructor for creating a command object
	 */
	public static function addCommandType($name, callable $constructor) {
		self::$commandTypes[strtoupper($name)] = $constructor;
	}

	/**
	 * Initialize the command backend, loading all known commands from Init.php
	 */
	public static function init() {
		require "Client/Init.php";
	}
}