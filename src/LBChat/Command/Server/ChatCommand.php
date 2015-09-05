<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;

class ChatCommand extends Command implements IServerCommand {
	protected $from;
	protected $to;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $from, ChatClient $to = null, $message) {
		parent::__construct($server);
		$this->from = $from;
		$this->to = $to;
		$this->message = $message;
	}

	public function execute(ChatClient $client) {
		$username = $this->from->getUsername();
		$display = $this->from->getDisplayName();
		$destination = "";
		$access = $this->from->getAccess();

		if ($this->to !== null)
			$destination = $this->to->getDisplayName();

		//TODO: Invisible user chats to mods+ only
		//TODO: Shadow banning

		//Broadcast a chat message to everyone
		//TODO: Send commands
		$client->send("CHAT $username $display $destination $access {$this->message}");
	}
}