<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

abstract class CommandFactory {
	/**
	 * @return Command
	 */
	public static function construct(ChatClient $client, ChatServer $server, $msg) {
		$words = explode(" ", $msg);
		if (count($words) == 0)
			return null;

		$first = array_shift($words);
		$rest = implode(" ", $words);

		switch ($first) {
		case "IDENTIFY": return new IdentifyCommand($client, $server, $rest);
		case "KEY":      return new KeyCommand     ($client, $server, $rest);
		case "CHAT":     return new ChatCommand    ($client, $server, $rest);
		}

		return null;
	}
}