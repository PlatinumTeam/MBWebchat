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
		$this->server->kickClient($this->recipiient, $this->time);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		if (count($words) < 1) {
			return InvalidCommand::createUsage($server, $client, "/kick <player> [time]");
		}

		$user = array_shift($words);
		$recipient = $server->findClient($user);

		$time = 60;
		if (count($words) > 0) {
			$time = (int)array_shift($words);
		}

		return new KickCommand($server, $client, $recipient, $time);
	}
}