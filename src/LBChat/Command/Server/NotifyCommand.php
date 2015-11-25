<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class NotifyCommand extends Command implements IServerCommand {
	protected $sender;
	protected $type;
	protected $access;
	protected $message;

	public function __construct(ChatServer $server, ChatClient $sender, $type, $access, $message) {
		parent::__construct($server);

		$this->sender = $sender;
		$this->type = $type;
		$this->access = $access;
		$this->message = $message;
	}

	public function execute(ChatClient $client) {
		//Only send to clients that are authorized
		if ($client->getAccess() >= $this->access) {
			$username = String::encodeSpaces($this->sender->getUsername());
			$display  = String::encodeSpaces($this->sender->getDisplayName());

			$client->send("NOTIFY $this->type $this->access $username $display $this->message");
		}
	}
}