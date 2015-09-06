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

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new PQCommand($server, $client);
	}
}