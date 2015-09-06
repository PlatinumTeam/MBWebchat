<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class KeyCommand extends Command implements IClientCommand {
	protected $key;

	public function __construct(ChatServer $server, ChatClient $client, $key) {
		parent::__construct($server, $client);
		$this->key = $key;
	}

	public function execute() {
		$this->client->login("key", $this->key);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new KeyCommand($server, $client, $rest);
	}
}
