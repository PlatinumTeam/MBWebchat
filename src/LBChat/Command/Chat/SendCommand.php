<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Misc\ServerChatClient;

class SendCommand extends Command implements IChatCommand {
	protected $message;

	public function __construct(ChatServer $server, ChatClient $client, $message) {
		parent::__construct($server, $client);
		$this->message = $message;
	}

	public function execute() {
		ServerChatClient::sendMessage(true, null, $this->message);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new SendCommand($server, $client, $rest);
	}

}