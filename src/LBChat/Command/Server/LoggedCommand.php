<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;

class LoggedCommand extends Command implements IServerCommand {

	/**
	 * Execute a server command on a specific client. The command should not be modified.
	 *
	 * @param ChatClient $client The client on which to execute the server command
	 */
	public function execute(ChatClient $client) {
		$client->send("LOGGED");
	}
}