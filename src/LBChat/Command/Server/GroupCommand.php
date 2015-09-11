<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Group\ChatGroup;

class GroupCommand extends Command implements IServerCommand {

	const ACTION_JOIN = "join";
	const ACTION_LEAVE = "leave";

	protected $action;
	protected $data;

	public function __construct(ChatServer $server, $action, $data = null) {
		parent::__construct($server);

		$this->action = $action;
		$this->data = $data;
	}


	/**
	 * Execute a server command on a specific client. The command should not be modified.
	 * @param ChatClient $client The client on which to execute the server command
	 */
	public function execute(ChatClient $client) {
		echo("Group command, action is {$this->action}\n");
		switch ($this->action) {
		case self::ACTION_JOIN:
			/* @var ChatGroup $group */
			$group = $this->data;

			echo("Joining client {$client->getUsername()} to group {$group->getName()}\n");
			$client->send("GROUP JOIN {$group->getName()}");
			break;
		case self::ACTION_LEAVE:
			/* @var ChatGroup $group */
			$group = $this->data;

			echo("Leaving client {$client->getUsername()} to group {$group->getName()}\n");
			$client->send("GROUP LEAVE {$group->getName()}");
			break;
		}
	}
}