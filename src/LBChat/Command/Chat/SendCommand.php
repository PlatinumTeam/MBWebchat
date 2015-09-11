<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\ChatCommandFactory;
use LBChat\Command\Client\ChatCommand;
use LBChat\Misc\ServerChatClient;

class SendCommand extends Command implements IChatCommand {
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, $message) {
		parent::__construct($server, $client);
		$this->message = $message;
	}

	public function execute() {
		//Try to create a command from the server
		$client = ServerChatClient::getClient();

		//If we can find a chat command (/something) then we should use that instead of the default
		// chat command behavior.
		$command = ChatCommandFactory::construct($this->server, $client, $this->message);
		if ($command === null) {
			$command = new ChatCommand($this->server, $client, null, $this->server->getGlobalGroup(), $this->message);
		}

		$command->execute();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new SendCommand($server, $client, $rest);
	}

}