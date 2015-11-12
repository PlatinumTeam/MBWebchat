<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server;

class AcceptTOSCommand extends Command implements IClientCommand {

	public function __construct(ChatServer $server, ChatClient $client) {
		parent::__construct($server, $client);
	}

	public function execute() {
		$this->client->acceptTOS();

		//Continue with the normal login process
		if ($this->client->onLogin()) {
			$command = new Server\IdentifyCommand($this->server, Server\IdentifyCommand::TYPE_SUCCESS);
			$command->execute($this->client);
		}
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new AcceptTOSCommand($server, $client);
	}

}
