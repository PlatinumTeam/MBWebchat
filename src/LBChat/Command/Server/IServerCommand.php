<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;

interface IServerCommand {
	public function execute(ChatClient $client);
}
