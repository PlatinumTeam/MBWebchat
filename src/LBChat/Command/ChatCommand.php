<?php
namespace LBChat\Command;

use LBChat\ChatClient;
use LBChat\ChatServer;

class ChatCommand extends Command {

	protected $message;

	public function __construct(ChatClient $client, ChatServer $server, $message) {
		parent::__construct($client, $server);
		$this->message = $message;
	}

	public function parse() {
		$username = $this->client->getUsername();
		$display = $this->client->getDisplayName();
		$destination = ""; //TODO: Add private messages
		$access = 0; //TODO: Add access support

		//TODO: Invisible user chats to mods+ only
		//TODO: Shadow banning

		//Broadcast a chat message to everyone
		//TODO: Send commands
		$this->server->broadcast("CHAT $username $display $destination $access {$this->message}");
	}
}