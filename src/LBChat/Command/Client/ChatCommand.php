<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\ChatCommandFactory;
use LBChat\Command\CommandFactory;
use LBChat\Command\Server;
use LBChat\Group\ChatGroup;
use LBChat\Utils\String;

class ChatCommand extends Command implements IClientCommand {

	/**
	 * @var ChatClient $recipient
	 */
	protected $recipient;
	/**
	 * @var ChatGroup $group
	 */
	protected $group;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient = null, ChatGroup $group,
	$message) {
		parent::__construct($server, $client);
		$this->recipient = $recipient;
		$this->group = $group;
		$this->message = $message;
	}

	public function execute() {
		//Don't let muted clients send messages
		if ($this->client->isMuted())
			return;

		$command = new Server\ChatCommand($this->server, $this->client, $this->recipient, $this->group,
			$this->message);
		$this->client->broadcastCommand($command);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		//<recipient> <group> <message ...>
		$words = String::getWordOptions($rest);
		//Pop the first word off and resolve it
		$recipient = $server->findClient(array_shift($words));
		$group = $server->getGroup(array_shift($words));

		if ($group === null) {
			$group = $server->getGlobalGroup();
		}

		$message = implode(" ", $words);

		//If we can find a chat command (/something) then we should use that instead of the default
		// chat command behavior.
		$command = ChatCommandFactory::construct($server, $client, $message);
		if ($command !== null) {
			return $command;
		}

		//No command, just send a basic chat message.
		return new ChatCommand($server, $client, $recipient, $group, $message);
	}
}
