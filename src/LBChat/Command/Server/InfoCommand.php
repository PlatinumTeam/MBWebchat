<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\Integration\LBServerSupport;

class InfoCommand extends Command implements IServerCommand {
	public function execute(ChatClient $client) {
		//If the client is a guest, they don't know their username
		if ($client->isGuest()) {
			$username = $client->getUsername();
			$client->send("INFO USERNAME $username");
		}

		//Basic user information
		$access    = $client->getAccess();
		$display   = $client->getDisplayName();
		$privilege = $client->getPrivilege();

		$client->send("INFO ACCESS $access");
		$client->send("INFO DISPLAY $display");
		$client->send("INFO PRIVILEGE $privilege");

		//Some global server stuff
		$welcome = $this->server->getServerSupport()->getWelcomeMessage($client->getLocation() === 3);
		$default = $this->server->getServerSupport()->getPreference("default"); //Default high score name

		$client->send("INFO WELCOME $welcome");
		$client->send("INFO DEFAULT $default");

		$this->sendHelp($client);
		$this->sendColors($client);
		$this->sendStatuses($client);
	}

	protected function sendHelp(ChatClient $client) {
		$info = $this->server->getServerSupport()->getPreference("chathelp");
		$format = $this->server->getServerSupport()->getPreference("chathelpformat");
		$cmdlist = $this->server->getServerSupport()->getPreference("chathelpcmdlist" . ($client->getPrivilege() > 0 ? "mod" : ""));

		$client->send("INFO HELP INFO $info\n");
		$client->send("INFO HELP FORMAT $format\n");
		$client->send("INFO HELP CMDLIST $cmdlist\n");
	}

	protected function sendColors(ChatClient $client) {
		//Color list from the server
		$colors = $this->server->getServerSupport()->getColorList();
		foreach ($colors as $item) {
			$ident = $item["ident"];
			$color = $item["color"];

			$client->send("COLOR $ident $color");
		}
	}

	protected function sendStatuses(ChatClient $client) {
		//Status list also controlled by the server
		$statuses = $this->server->getServerSupport()->getStatusList();
		foreach ($statuses as $item) {
			$status  = $item["status"];
			$display = $item["display"];
			$client->send("STATUS $status $display");
		}
	}
}