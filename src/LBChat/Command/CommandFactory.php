<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Client\IClientCommand;

abstract class CommandFactory {
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

		switch ($first) {
		case "CHAT":     return new Client\ChatCommand    ($client, $server, $rest);
		case "IDENTIFY": return new Client\IdentifyCommand($client, $server, $rest);
		case "LOCATION": return new Client\LocationCommand($client, $server, $rest);
		case "KEY":      return new Client\KeyCommand     ($client, $server, $rest);
		case "PING":     return new Client\PingCommand    ($client, $server, $rest);
		case "USERLIST": return new Client\UserlistCommand($client, $server       );
		}

		return null;
	}
}