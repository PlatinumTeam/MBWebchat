<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use LBChat\Database\Database;
use LBChat\Database\SQLChatServer;
use LBChat\Command\CommandFactory;

require dirname(__DIR__) . "/vendor/autoload.php";

CommandFactory::init();

$databases = array(
	"platinum" => new Database("platinum"),
	"joomla" => new Database("joomla")
);

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new SQLChatServer($databases)
		)
	),
	39002
);

$server->run();
