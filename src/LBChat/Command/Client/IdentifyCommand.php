<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

class IdentifyCommand extends Command implements IClientCommand {

	const GUEST_LOGIN_NAME = "Guest";
	protected $username;

	public function __construct(ChatServer $server, ChatClient $client, $username) {
		parent::__construct($server, $client);
		$this->username = $username;
	}

	public function execute() {
		if ($this->username === self::GUEST_LOGIN_NAME) {
			//They're a guest; they need a guest name
			$this->client->setGuest();
		} else {
			$this->client->setUsername($this->username);
		}
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		//Don't let clients identify twice
		if ($client->getLoggedIn())
			return null;

		return new IdentifyCommand($server, $client, $rest);
	}
}
