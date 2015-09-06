<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class UserlistCommand extends Command implements IClientCommand {

	public function execute() {
		//Send the client the user list
		$this->server->sendUserlist($this->client);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new UserlistCommand($server, $client);
	}

}
