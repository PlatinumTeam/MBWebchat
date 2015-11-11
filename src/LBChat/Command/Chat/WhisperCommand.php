<?php

namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Utils\String;

class WhisperCommand extends Command implements IChatCommand {

	protected $recipient;
	protected $recipientarray;

	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient = null, ChatClient $recipientarray = null, $message) {
		parent::__construct($server, $client);
		if (strpos($recipient, ",") === false) $this->recipient = $recipient;
		else{
			$this->recipientarray = String::token_to_array($recipient);
		}
		$this->message = $message;
	}

	public function execute() {
		if ($this->recipient != null){
			$message = "/whisper {$this->recipient->getUsername()} {$this->message}";
			$chat = new ChatCommand($this->server, $this->client, $this->recipient, $message);
			$chat->execute($this->recipient);
		}
		else{
			foreach ($this->recipientarray as $recipient){
				$message = "/whisper {$recipient->getUsername()} {$this->message}";
				$chat = new ChatCommand($this->server, $this->client, $recipient, $message);
				$chat->execute($this->recipient);
			}
		}
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);
		if (count($words) < 2) {
			return InvalidCommand::createUsage($server, $client, "/whisper [<player> or <player1>,<player2>,etc...] <message>");
		}

		$user = array_shift($words);
		$recipient = $server->findClient($user);
		if ($recipient === null) {
			return InvalidCommand::createUnknownUser($server, $client, $user);
		}
		$message = implode(" ", $words);
		return new WhisperCommand($server, $client, $recipient, $message);
	}
}