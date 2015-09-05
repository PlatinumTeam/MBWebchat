<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class LocationCommand extends Command {

	protected $location;

	public function __construct(ChatClient $client, ChatServer $server, $location) {
		parent::__construct($client, $server);
		$this->location = $location;
	}

	public function parse() {
		$this->client->setLocation($this->location);
	}
}