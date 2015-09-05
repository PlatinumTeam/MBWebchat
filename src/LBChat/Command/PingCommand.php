<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class PingCommand extends Command {

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