<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\Integration\LBServerSupport;

class InfoCommand extends Command implements IServerCommand {
	public function execute(ChatClient $client) {
		$access  = $client->getAccess();
		$display = $client->getDisplayName();

		$client->send("INFO ACCESS $access");
		$client->send("INFO DISPLAY $display");

		$colors = LBServerSupport::getColorList();
		foreach ($colors as $item) {
			$ident = $item["ident"];
			$color = $item["color"];

			$client->send("COLOR $ident $color");
		}

		$statuses = LBServerSupport::getStatusList();
		foreach ($statuses as $item) {
			$status  = $item["status"];
			$display = $item["display"];
			$client->send("STATUS $status $display");
		}
	}
}