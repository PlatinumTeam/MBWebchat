<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;

class StopCommand extends Command implements IChatCommand {

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		exit(0);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new StopCommand($server, $client);
	}
}