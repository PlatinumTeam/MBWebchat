<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Misc\ServerChatClient;
use LBChat\Utils\String;

class InvalidCommand extends Command implements IChatCommand {
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, $message) {
		parent::__construct($server, $client);
		$this->message = $message;
	}

	public function execute() {
		//Send them a quiet whisper
		$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this->client), $this->message);
		$chat->execute();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		//They just sent a "/" message
		if (count($words) == 0)
			return null;

		$message = "Invalid command: " . array_shift($words);

		//Command is the first word
		return new InvalidCommand($server, $client, $message);
	}

	public static function createUsage(ChatServer $server, ChatClient $client, $usage) {
		return new InvalidCommand($server, $client, "Invalid command syntax. Usage: {$usage}");
	}

	public static function createUnknownUser(ChatServer $server, ChatClient $client, $user) {
		return new InvalidCommand($server, $client, "Invalid target. Could not find user \"{$user}\"");
	}

	public static function createAccessDenied(ChatServer $server, ChatClient $client, $message = "perform this action") {
		return new InvalidCommand($server, $client, "Access Denied. You are not allowed to {$message}.");
	}

	public static function createGeneric(ChatServer $server, ChatClient $client, $message) {
		return new InvalidCommand($server, $client, "Invalid command. {$message}");
	}
}