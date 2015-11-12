<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class FriendCommand extends Command implements IServerCommand {
	const TYPE_START = 0;
	const TYPE_NAME = 1;
	const TYPE_DONE = 2;
	const TYPE_ADDED = 3;
	const TYPE_DELETED = 4;
	const TYPE_FAILED = 5;

	protected $type;
	protected $friend;
	protected $friendDisplay;

	public function __construct(ChatServer $server, $type, $friend = null, $friendDisplay = null) {
		parent::__construct($server);

		$this->type = $type;
		$this->friend = $friend;
		$this->friendDisplay = $friendDisplay;
	}
	/**
	 * Execute a server command on a specific client. The command should not be modified.
	 * @param ChatClient $client The client on which to execute the server command
	 */
	public function execute(ChatClient $client) {
		switch ($this->type) {
		case self::TYPE_START:
			$client->send("FRIEND START");
			break;
		case self::TYPE_NAME:
			$username = String::encodeSpaces($this->friend);
			$display  = String::encodeSpaces($this->friendDisplay);
			$client->send("FRIEND NAME {$username} {$display}");
			break;
		case self::TYPE_DONE:
			$client->send("FRIEND DONE");
			break;
		case self::TYPE_ADDED:
			$client->send("FRIEND ADDED");
			break;
		case self::TYPE_DELETED:
			$client->send("FRIEND DELETED");
			break;
		case self::TYPE_FAILED:
			$client->send("FRIEND FAILED");
			break;
		}
	}
}