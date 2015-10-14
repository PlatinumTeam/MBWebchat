<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;

class AcceptTOSCommand extends Command implements IClientCommand {

	public function __construct(ChatServer $server, ChatClient $client) {
		parent::__construct($server, $client);
	}

	public function execute() {
		//Continue with the normal login process
		$this->client->onLogin();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		return new AcceptTOSCommand($server, $client);
	}

}
