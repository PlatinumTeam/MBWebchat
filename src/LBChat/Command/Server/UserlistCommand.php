<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;

class UserlistCommand extends Command implements IServerCommand {
	protected $clients;

	public function __construct(ChatServer $server, $clients) {
		parent::__construct($server);
		$this->clients = $clients;
	}

	public function execute(ChatClient $client) {
		$this->start($client);
		foreach ($this->clients as $cl) {
			$this->send($client, $cl);
		}
		$this->done($client);
	}
	
	protected function start(ChatClient $client) {
		$client->send("USER START");
	}
	protected function send(ChatClient $client, ChatClient $other) {
		$username = $client->getUsername();
		$display  = $client->getDisplayName();
		$access   = $client->getAccess();
		$location = $client->getLocation();

		$color  = $client->getColor();
		$titles = $client->getTitles();
		$flair  = $titles[0];
		$prefix = $titles[1];
		$suffix = $titles[2];

		$client->send("USER COLORS $username $color $color $color\n");
		$client->send("USER TITLES $flair $prefix $suffix\n");
		$client->send("USER NAME $username $access $location $display\n");
	}
	protected function done(ChatClient $client) {
		$client->send("USER DONE");
	}
}