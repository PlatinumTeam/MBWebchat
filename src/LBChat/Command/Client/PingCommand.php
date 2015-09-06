<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class PingCommand extends Command implements IClientCommand {

	protected $data;

	public function __construct(ChatServer $server, ChatClient $client, $data) {
		parent::__construct($server, $client);
		$this->data = $data;
	}

	public function execute() {
		//TODO: Send commands
		$this->client->send("PONG {$this->data}");
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new PingCommand($server, $client, $rest);
	}

}
