<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class VerifyCommand extends Command implements IClientCommand {

	protected $version;
	protected $password;

	public function __construct(ChatServer $server, ChatClient $client, $version, $password) {
		parent::__construct($server, $client);

		$this->version = $version;
		$this->password = $password;
	}

	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute() {
		$this->client->login("password", String::degarbledeguck($this->password));
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		//Don't let clients verify twice.
		if ($client->getLoggedIn())
			return null;
		//Don't let unidentified clients verify
		if ($client->getUsername() === "")
			return null;

		$words = String::getWordOptions($rest);
		$version  = (int)(array_shift($words));
		$password =       array_shift($words);
		return new VerifyCommand($server, $client, $version, $password);
	}
}