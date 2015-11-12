<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\FriendCommand;
use LBChat\Command\Server\FriendCommandType;

class FriendDelCommand extends Command implements IClientCommand {

	protected $friend;

	public function __construct(ChatServer $server, ChatClient $client, $friend) {
		parent::__construct($server, $client);

		$this->friend = $friend;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$this->client->removeFriend($this->friend);
		$command = new FriendCommand($this->server, FriendCommand::TYPE_DELETED, $this->friend);
		$command->execute($this->client);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new FriendDelCommand($server, $client, $rest);
	}
}