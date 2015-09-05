<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Client\IClientCommand;

abstract class CommandFactory {
	/**
	 * @return IClientCommand
	 */
	public static function construct(ChatClient $client, ChatServer $server, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		switch ($first) {
		case "IDENTIFY": return new Client\IdentifyCommand($client, $server, $rest);
		case "KEY":      return new Client\KeyCommand     ($client, $server, $rest);
		case "CHAT":     return new Client\ChatCommand    ($client, $server, $rest);
		case "USERLIST": return new Client\UserlistCommand($client, $server       );
		case "PING":     return new Client\PingCommand    ($client, $server, $rest);
		}

		return null;
	}
}