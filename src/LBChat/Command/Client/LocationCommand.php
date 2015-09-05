<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;

class LocationCommand extends Command implements IClientCommand {

	protected $location;

	public function __construct(ChatClient $client, ChatServer $server, $location) {
		parent::__construct($client, $server);
		$this->location = $location;
	}

	public function parse() {
		$this->client->setLocation($this->location);
	}
}