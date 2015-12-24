<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Misc\DummyChatClient;
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
		$this->server->banClient($this->recipient, $this->days);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		if (count($words) < 1) {
			return InvalidCommand::createUsage($server, $client, "/ban <player> [days]");
		}

		$user = array_shift($words);
		$recipient = $server->findClient($user);

		//If they're not online, make a dummy and ban them
		if ($recipient === null) {
			$recipient = new DummyChatClient($server, $user, 0);
		}

		//Can't ban people with more power than you
		if (!$client->checkPrivilege($recipient->getPrivilege())) {
			return InvalidCommand::createAccessDenied($server, $client, "ban users with greater access");
		}

		$days = -1; //Infinite
		if (count($words) > 0) {
			$days = (int)array_shift($words);
		}

		return new BanCommand($server, $client, $recipient, $days);
	}
}