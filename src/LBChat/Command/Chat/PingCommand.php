<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Misc\ServerChatClient;

class PingCommand extends Command implements IChatCommand {

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$whisper = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this->client), "Pong!");
		$whisper->execute();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new PingCommand($server, $client);
	}
}