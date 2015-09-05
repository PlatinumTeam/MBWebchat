<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Command;

class PingCommand extends Command implements IClientCommand {

	protected $data;

	public function __construct(ChatClient $client, ChatServer $server, $data) {
		parent::__construct($client, $server);
		$this->data = $data;
	}

	public function parse() {
		//TODO: Send commands
		$this->client->send("PONG {$this->data}");
	}

}