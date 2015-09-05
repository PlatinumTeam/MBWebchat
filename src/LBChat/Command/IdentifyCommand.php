<?php
namespace LBChat\Command;

use LBChat\ChatClient;

class IdentifyCommand extends Command {

	protected $username;

	public function __construct(ChatClient $client, $username) {
		parent::__construct($client);
		$this->username = $username;
	}

	public function parse() {
		$this->client->setUsername($this->username);
	}
}
