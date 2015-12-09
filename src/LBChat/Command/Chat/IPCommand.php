<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Misc\ServerChatClient;
use LBChat\Utils\String;

class IPCommand extends Command implements IChatCommand {

	/**
	 * @var ChatClient $target
	 */
	protected $target;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $target) {
		parent::__construct($server, $client);
		$this->target = $target;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$ip = $this->target->getAddress();

		//Be personal with yourself
		$message = ($this->target === $this->client ? "Your IP is {$ip}" : "{$this->target->getDisplayName()}'s IP is {$ip}");

		//Send them a whisper
		$command = new WhisperCommand($this->server, ServerChatClient::getClient(), [$this->client], $message);
		$command->execute();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		//Only allow mods to get other users' IPs
		if (count($words) === 0 || $client->getPrivilege() < 1) {
			//Give them an IPCommand with their own user
			return new IPCommand($server, $client, $client);
		}

		$user = array_shift($words);
		$target = $server->findClient($user);

		//Can't find them
		if ($target === null) {
			return InvalidCommand::createUnknownUser($server, $client, $user);
		}

		//Can find them
		return new IPCommand($server, $client, $target);
	}
}