<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Group\ChatGroup;
use LBChat\Utils\String;

class GroupCommand extends Command implements IChatCommand {

	const ACTION_JOIN = "join";
	const ACTION_LEAVE = "leave";

	protected $action;
	protected $data;

	public function __construct(ChatServer $server, ChatClient $client, $action, $data = null) {
		parent::__construct($server, $client);

		$this->action = $action;
		$this->data = $data;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		switch ($this->action) {
		case self::ACTION_JOIN:
			/* @var ChatGroup $group */
			$group = $this->data;
			$group->addClient($this->client);
			break;
		case self::ACTION_LEAVE:
			/* @var ChatGroup $group */
			$group = $this->data;
			$group->removeClient($this->client);
			break;
		}
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		if (count($words) === 0) {
			return InvalidCommand::createUsage($server, $client, "/group <command>");
		}

		$command = array_shift($words);

		echo("Group command: \"$command\"\n");

		switch ($command) {
		case self::ACTION_JOIN:
		case self::ACTION_LEAVE:
			// /group join <groupname>
			// /group leave <groupname>


			$name = implode(" ", $words);
			echo("Joining/leaving group {$name}\n");

			//Try to find the group in the server
			$group = $server->getGroup($name);

			if ($group === null) {
				return InvalidCommand::createGeneric($server, $client, "Could not find group.");
			}

			return new GroupCommand($server, $client, $command, $group);
		}

	}
}