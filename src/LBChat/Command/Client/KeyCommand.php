<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;

class KeyCommand extends Command implements IClientCommand {
	protected $key;

	public function __construct(ChatClient $client, ChatServer $server, $key) {
		parent::__construct($client, $server);
		$this->key = $key;
	}

	public function execute() {
		$this->client->login("key", $this->key);
	}
}