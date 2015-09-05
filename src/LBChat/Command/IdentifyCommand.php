<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class IdentifyCommand extends Command {

	protected $username;

	public function __construct(ChatClient $client, ChatServer $server, $username) {
		parent::__construct($client, $server);
		$this->username = $username;
	}

	public function parse() {
		$this->client->setUsername($this->username);
	}
}
