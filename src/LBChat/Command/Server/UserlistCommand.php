<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Utils\String;

class UserlistCommand extends Command implements IServerCommand {
	protected $clients;

	public function __construct(ChatServer $server, $clients) {
		parent::__construct($server);
		$this->clients = $clients;
	}

	public function execute(ChatClient $client) {
		$this->start($client);
		foreach ($this->clients as $cl) {
			/* @var ChatClient $cl */
			if ($cl->getVisible())
				$this->send($client, $cl);
		}
		$this->done($client);
	}
	
	protected function start(ChatClient $client) {
		$client->send("USER START");
	}
	protected function send(ChatClient $client, ChatClient $other) {
		$username = String::encodeSpaces($other->getUsername());
		$display  = String::encodeSpaces($other->getDisplayName());
		$access   = $other->getAccess();
		$location = $other->getLocation();

		$color  = $other->getColor();
		$titles = $other->getTitles();
		$flair  = String::encodeSpaces($titles[0]);
		$prefix = String::encodeSpaces($titles[1]);
		$suffix = String::encodeSpaces($titles[2]);

		$client->send("USER INFO $username $access $location $display $color $flair $prefix $suffix");
		$client->send("USER COLORS $username $color $color $color");
		$client->send("USER TITLES $flair $prefix $suffix");
		$client->send("USER NAME $username $access $location $display");
	}
	protected function done(ChatClient $client) {
		$client->send("USER DONE");
	}
}