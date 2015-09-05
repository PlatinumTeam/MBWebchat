<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server;

class ChatCommand extends Command implements IClientCommand {

	protected $message;

	public function __construct(ChatClient $client, ChatServer $server, $message) {
		parent::__construct($client, $server);
		$this->message = $message;
	}

	public function execute() {
		$command = new Server\ChatCommand($this->server, $this->client, null, $this->message);
		$this->server->broadcastCommand($command);
	}
}