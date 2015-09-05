<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class UserlistCommand extends Command {

	public function parse() {
		//Send the client the user list
		$this->server->sendUserlist($this->client);
	}

}