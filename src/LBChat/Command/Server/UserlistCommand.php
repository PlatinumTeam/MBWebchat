<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;

class UserlistCommand extends Command implements IServerCommand {
	public function execute(ChatClient $client) {

	}
	public function start(ChatClient $client) {
		$client->send("USER START");
	}
	public function send(ChatClient $client, ChatClient $other) {
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
	public function done(ChatClient $client) {
		$client->send("USER DONE");
	}
}