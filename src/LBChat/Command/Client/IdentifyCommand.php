<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class IdentifyCommand extends Command implements IClientCommand {

	protected $username;

	public function __construct(ChatServer $server, ChatClient $client, $username) {
		parent::__construct($server, $client);
		$this->username = $username;
	}

	public function execute() {
		$this->client->setUsername($this->username);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new IdentifyCommand($server, $client, $rest);
	}
}
