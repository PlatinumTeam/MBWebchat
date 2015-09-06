<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Misc\ServerChatClient;

class SendCommand extends Command implements IChatCommand {
	protected $message;

	public function __construct(ChatClient $client, ChatServer $server, $message) {
		parent::__construct($client, $server);
		$this->message = $message;
	}

	public function execute() {
		ServerChatClient::sendMessage(true, null, $this->message);
	}

	public static function init(ChatClient $client, ChatServer $server, $rest) {
		return new SendCommand($client, $server, $rest);
	}

}