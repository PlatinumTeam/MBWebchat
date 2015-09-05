<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use LBChat\ChatServer;

require dirname(__DIR__) . "/vendor/autoload.php";

\LBChat\Command\CommandFactory::init();

$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			new ChatServer()
		)
	),
	28002
);

$server->run();
