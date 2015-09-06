<?php

namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Utils\String;

class WhisperCommand extends Command implements IChatCommand {

	protected $recipient;

	protected $message;

	public function __construct(ChatClient $client, ChatServer $server, ChatClient $recipient = null, $message) {
		parent::__construct($client, $server);
		$this->recipient = $recipient;
		$this->message = $message;
	}

	public function execute() {
		$message = "/whisper {$this->recipient->getUsername()} {$this->message}";
		$chat = new ChatCommand($this->server, $this->client, $this->recipient, $message);
		$chat->execute($this->recipient);
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		$words = explode(" ", $rest);
		$recipient = $server->findClient(String::decodeSpaces(array_shift($words)));
		if ($recipient === null)
			return null;
		$message = implode(" ", $words);
		return new WhisperCommand($client, $server, $recipient, $message);
	}
}