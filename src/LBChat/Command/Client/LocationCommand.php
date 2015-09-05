<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;
use LBChat\Command\Server\NotifyCommand;

class LocationCommand extends Command implements IClientCommand {

	protected $location;

	public function __construct(ChatClient $client, ChatServer $server, $location) {
		parent::__construct($client, $server);
		$this->location = (int)$location;
	}

	public function execute() {
		$this->client->setLocation($this->location);
		$this->server->broadcastCommand(new NotifyCommand($this->server, $this->client, "setlocation", 0, $this->location));
		$this->server->sendAllUserlists();
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		return new LocationCommand($client, $server, $rest);
	}
}
