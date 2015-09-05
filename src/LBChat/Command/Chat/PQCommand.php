<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;

class PQCommand extends Command implements IChatCommand {
	public function execute() {
		$chat = new ChatCommand($this->server, $this->client, null, "PQ!");
		$this->server->broadcastCommand($chat);
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		return new PQCommand($client, $server);
	}
}