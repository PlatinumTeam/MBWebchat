<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\ChatCommandFactory;
use LBChat\Command\CommandFactory;
use LBChat\Command\Server;
use LBChat\Utils\String;

class ChatCommand extends Command implements IClientCommand {

	protected $recipient;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient = null, $message) {
		parent::__construct($server, $client);
		$this->message = $message;
	}

	public function execute() {
		//Don't let muted clients send messages
		if ($this->client->isMuted())
			return;

		$command = new Server\ChatCommand($this->server, $this->client, $this->recipient, $this->message);
		$this->server->broadcastCommand($command);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		//<recipient> <message ...>
		$words = String::getWordOptions($rest);
		//Pop the first word off and resolve it
		$recipient = $server->findClient(array_shift($words));
		$message = implode(" ", $words);

		//If we can find a chat command (/something) then we should use that instead of the default
		// chat command behavior.
		$command = ChatCommandFactory::construct($server, $client, $message);
		if ($command !== null) {
			return $command;
		}

		//No command, just send a basic chat message.
		return new ChatCommand($server, $client, $recipient, $message);
	}
}
