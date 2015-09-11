<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Group\ChatGroup;
use LBChat\Utils\String;

class ChatCommand extends Command implements IServerCommand {
	protected $from;
	protected $to;
	protected $group;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $from, ChatClient $to = null, ChatGroup $group,
$message) {
		parent::__construct($server);
		$this->from = $from;
		$this->to = $to;
		$this->group = $group;
		$this->message = $message;
	}

	public function execute(ChatClient $client) {
		$username    = String::encodeSpaces($this->from->getUsername());
		$display     = String::encodeSpaces($this->from->getDisplayName());
		$destination = "";
		$access      = $this->from->getAccess();
		$group       = String::encodeSpaces($this->group->getName());
		$message     = urlencode($this->message);

		if ($this->to !== null)
			$destination = String::encodeSpaces($this->to->getUsername());

		//TODO: Invisible user chats to mods+ only
		//TODO: Shadow banning

		//Broadcast a chat message to everyone
		$client->send("CHAT $username $display $destination $access $group $message");
	}
}