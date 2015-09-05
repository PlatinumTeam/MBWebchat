<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;

class UserlistCommand extends Command implements IClientCommand {

	public function execute() {
		//Send the client the user list
		$this->server->sendUserlist($this->client);
	}

}