<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\FriendCommandType;

class FriendCommand extends Command implements IClientCommand {

	protected $friend;

	public function __construct(ChatServer $server, ChatClient $client, $friend) {
		parent::__construct($server, $client);

		$this->friend = $friend;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$this->client->addFriend($this->friend);
		$command = new \LBChat\Command\Server\FriendCommand($this->server, \LBChat\Command\Server\FriendCommand::TYPE_ADDED, $this->friend);
		$command->execute($this->client);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new FriendCommand($server, $client, $rest);
	}
}