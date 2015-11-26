<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class KickCommand extends Command implements IChatCommand {

	/**
	 * @var ChatClient $recipient
	 */
	protected $recipient;

	/**
	 * @var int $time
	 */
	protected $time;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient, $time) {
		parent::__construct($server, $client);

		$this->recipient = $recipient;
		$this->time = $time;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$this->server->kickClient($this->recipient, $this->time);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		if (count($words) < 1) {
			return InvalidCommand::createUsage($server, $client, "/kick <player> [time]");
		}

		$user = array_shift($words);
		$recipient = $server->findClient($user);

		//Can't kick people who aren't online
		if ($recipient === null) {
			return InvalidCommand::createUnknownUser($server, $client, $user);
		}

		//Can't kick people with more power than you
		if (!$client->checkPrivilege($recipient->getPrivilege())) {
			return InvalidCommand::createAccessDenied($server, $client, "kick users with greater access");
		}

		$time = 60;
		if (count($words) > 0) {
			$time = (int)array_shift($words);
		}

		return new KickCommand($server, $client, $recipient, $time);
	}
}