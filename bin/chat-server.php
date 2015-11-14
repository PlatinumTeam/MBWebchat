<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use LBChat\Database\Database;
use LBChat\Database\SQLChatServer;
use LBChat\Command\CommandFactory;
use LBChat\Command\ChatCommandFactory;
use LBChat\Integration\JoomlaUserSupport;
use LBChat\Integration\LBUserSupport;
use LBChat\Integration\LBServerSupport;
use LBChat\Misc\PlainServer;

//Load up 3rd party libraries
require dirname(__DIR__) . "/vendor/autoload.php";

//Initialize the command factories so we can recognize commands
CommandFactory::init();
ChatCommandFactory::init();

//Array of databases that we use in this server
$databases = array(
	"platinum" => new Database("platinum"),
	"joomla" => new Database("joomla")
);

//Assign the user support classes their databases so we can access the site data
$userSupport = new JoomlaUserSupport($databases["joomla"], new LBUserSupport($databases["platinum"]));
$serverSupport = new LBServerSupport($databases["platinum"]);

//The main chat server
$chatServer = new SQLChatServer($serverSupport, $userSupport, $databases);

//Start a listening server that accepts HTTP and WebSocket connections
$server = IoServer::factory(
	new HttpServer(
		new WsServer(
			$chatServer
		)
	),
	39002
);
$server2 = IoServer::factory(
	new PlainServer(
		$chatServer
	),
	39003,
	"0.0.0.0",
	$server->loop
);

//Give the server the scheduler so it can do callbacks
$chatServer->setScheduler($server->loop);

//Start up any timers
$chatServer->start();

//Run the server
$server->run();