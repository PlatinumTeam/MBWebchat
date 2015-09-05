<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class KeyCommand extends Command {
	protected $key;

	public function __construct(ChatClient $client, ChatServer $server, $key) {
		parent::__construct($client, $server);
		$this->key = $key;
	}

	public function parse() {
		$this->client->login("key", $this->key);
	}
}