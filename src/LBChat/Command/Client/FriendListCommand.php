<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\FriendCommand;
use LBChat\Command\Server\FriendCommandType;

class FriendListCommand extends Command implements IClientCommand {

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		//Send them a START at the beginning
		$command = new FriendCommand($this->server, FriendCommand::TYPE_START);
		$command->execute($this->client);

		//Get the client's friend list
		$list = $this->client->getFriendList();

		//And send it all
		foreach ($list as $info) {
			/* @var array $info */
			$command = new FriendCommand($this->server, FriendCommand::TYPE_NAME, $info["username"], $info["display"]);
			$command->execute($this->client);
		}

		//Send them a DONE at the end
		$command = new FriendCommand($this->server, FriendCommand::TYPE_DONE);
		$command->execute($this->client);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new FriendListCommand($server, $client);
	}
}