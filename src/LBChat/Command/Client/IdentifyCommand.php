<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class IdentifyCommand extends Command implements IClientCommand {

	protected $username;

	public function __construct(ChatClient $client, ChatServer $server, $username) {
		parent::__construct($client, $server);
		$this->username = $username;
	}

	public function execute() {
		$this->client->setUsername($this->username);
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		return new IdentifyCommand($client, $server, $rest);
	}
}
