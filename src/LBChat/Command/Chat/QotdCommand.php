<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\NotifyCommand;
use LBChat\Utils\String;

class QotdCommand extends Command implements IChatCommand {

	/**
	 * @var string $sender
	 */
	protected $sender;
	/**
	 * @var string $message
	 */
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, $sender, $message) {
		parent::__construct($server, $client);

		$this->sender = $sender;
		$this->message = $message;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		//Update the server
		$this->server->getServerSupport()->setQotd($this->sender, $this->message);

		//Send everyone a notification of the update
		$command = new NotifyCommand($this->server, $this->client, "qotdupdate", 0, $this->message);
		$this->server->broadcastCommand($command);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		//Don't allow users to access this command
		if ($client->getPrivilege() == 0) {
			return InvalidCommand::createAccessDenied($server, $client, "update the QOTD");
		}

		$words = String::getWordOptions($rest);

		//Make sure they used it right
		if (count($words) < 2) {
			return InvalidCommand::createUsage($server, $client, "/qotd <sender> <message>");
		}

		$sender = array_shift($words);
		$message = implode(" ", $words);

		return new QotdCommand($server, $client, $sender, $message);
	}
}