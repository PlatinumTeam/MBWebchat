<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Group\ChatGroup;
use LBChat\Utils\String;

class GroupCommand extends Command implements IServerCommand {

	const ACTION_JOIN = "join";
	const ACTION_LEAVE = "leave";

	const ACTION_LOGIN = "login";
	const ACTION_LOGOUT = "logout";

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
			$name = String::encodeSpaces($group->getName());
			$client->send("GROUP JOIN $name");
			break;
		case self::ACTION_LEAVE:
			/* @var ChatGroup $group */
			$group = $this->data;
			$name = String::encodeSpaces($group->getName());
			$client->send("GROUP LEAVE $name");
			break;
		case self::ACTION_LOGIN:
			/* @var ChatClient $user */
			$user = $this->data;
			$name = String::encodeSpaces($user->getUsername());
			$display = String::encodeSpaces($user->getDisplayName());
			$client->send("GROUP LOGIN $name $display");
			break;
		case self::ACTION_LOGOUT:
			/* @var ChatClient $user */
			$user = $this->data;
			$name = String::encodeSpaces($user->getUsername());
			$display = String::encodeSpaces($user->getDisplayName());
			$client->send("GROUP LOGOUT $name $display");
			break;
		}
	}
}