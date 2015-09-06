<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;

interface IClientCommand {
	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute();

	public static function init(ChatServer $server, ChatClient $client, $rest);
}
