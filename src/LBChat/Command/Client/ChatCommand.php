<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;
use LBChat\Command\Server;
use LBChat\Utils\String;

class ChatCommand extends Command implements IClientCommand {

	protected $recipient;
	protected $message;

	public function __construct(ChatClient $client, ChatServer $server, ChatClient $recipient = null, $message) {
		parent::__construct($client, $server);
		$this->message = $message;
	}

	public function execute() {
		$command = new Server\ChatCommand($this->server, $this->client, $this->recipient, $this->message);
		$this->server->broadcastCommand($command);
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		//CHAT <recipient> <message ...>
		$words = explode(" ", $rest);
		//Pop the first word off and resolve it
		$recipient = $server->findClient(String::decodeSpaces(array_shift($words)));
		$message = implode(" ", $words);

		//TODO: Better chat commands here
		if ($words[0] === "/whisper") {
			$recipient = $server->findClient(String::decodeSpaces($words[1]));
		}

		return new ChatCommand($client, $server, $recipient, $message);
	}
}
