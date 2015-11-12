<?php

namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Utils\String;

class WhisperCommand extends Command implements IChatCommand {

	protected $recipients;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, array $recipients = null, $message) {
		parent::__construct($server, $client);
		$this->recipients = $recipients;
		$this->message = $message;
	}

	public function execute() {
		foreach ($this->recipients as $recipient) {
			/* @var ChatClient $recipient */
			$message = "/whisper {$recipient->getUsername()} {$this->message}";
			$chat = new ChatCommand($this->server, $this->client, $recipient, $message);
			$chat->execute($recipient);
		}
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);
		if (count($words) < 2) {
			return InvalidCommand::createUsage($server, $client, "/whisper [<player> or <player1>,<player2>,etc...] <message>");
		}

		$user = array_shift($words);
		if (strpos($user, ",") === false) {
			$recipients = array($user);
		} else {
			$recipients = explode(",", $user);
		}

		//Convert player names to clients
		$recipients = array_map(function($user) use($server) {
			return $server->findClient($user);
		}, $recipients);

		//Remove any null players
		$recipients = array_filter($recipients);

		//Don't let us message the same person twice
		$recipients = array_unique($recipients);

		//If nobody was added, fail
		if (count($recipients) === 0) {
			return InvalidCommand::createUnknownUser($server, $client, $user);
		}
		$message = implode(" ", $words);
		return new WhisperCommand($server, $client, $recipients, $message);
	}
}