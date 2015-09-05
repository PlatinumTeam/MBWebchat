<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use LBChat\Database\Database;
use LBChat\Database\SQLChatServer;
use LBChat\Command\CommandFactory;

require dirname(__DIR__) . "/vendor/autoload.php";

CommandFactory::init();

$database = new Database();
$database->connect();

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new SQLChatServer($database)
		)
	),
	39002
);

$server->run();
