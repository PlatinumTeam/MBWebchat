<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Misc\ServerChatClient;

class PQCommand extends Command implements IChatCommand {
	public function execute() {
		$chat = new ChatCommand($this->server, ServerChatClient::getClient(), null, "PQ WHERe?");
		$this->server->broadcastCommand($chat);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new PQCommand($server, $client);
	}
}