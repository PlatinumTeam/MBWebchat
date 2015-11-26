<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class BanCommand extends Command implements IChatCommand {

	/**
	 * @var ChatClient $recipient
	 */
	protected $recipient;

	/**
	 * @var int $days
	 */
	protected $days;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient, $days) {
		parent::__construct($server, $client);

		$this->recipient = $recipient;
		$this->days = $days;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$this->server->banClient($this->client, $this->days);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		if (count($words) < 1) {
			return InvalidCommand::createUsage($server, $client, "/ban <player> [days]");
		}

		$user = array_shift($words);
		$recipient = $server->findClient($user);

		$days = -1; //Infinite
		if (count($words) > 0) {
			$days = (int)array_shift($words);
		}

		return new BanCommand($server, $client, $recipient, $days);
	}
}