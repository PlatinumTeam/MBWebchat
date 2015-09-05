<?php
namespace LBChat\Command\Client;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\CommandFactory;

function _addCommands($array) {
	foreach ($array as $class) {
		if (is_array($class)) {
			_addCommand($class[0], $class[1]);
		} else {
			_addCommand($class);
		}
	}
}

function _addCommand($name, $class = null) {
	if ($class === null)
		$class = "LBChat\\Command\\Client\\{$name}Command";

	CommandFactory::addCommandType($name, function(ChatClient $client, ChatServer $server, $rest) use ($class) {
		return call_user_func(array($class, "init"), $client, $server, $rest);
	});
}

//Add all the commands
_addCommands(
	array(
		"Chat",
		"Identify",
		"Key",
		"Location",
		"Ping",
		"Userlist"
	)
);
