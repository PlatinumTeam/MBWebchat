<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\ChatCommandFactory;
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
		$class = "LBChat\\Command\\Chat\\{$name}Command";

	ChatCommandFactory::addCommandType($name, function(ChatServer $server, ChatClient $client, $rest) use ($class) {
		return call_user_func(array($class, "init"), $server, $client, $rest);
	});
}

//Add all the commands
_addCommands(
	array(
		"PQ",
		"Whisper",
		"Send",
		"Mute"
	)
);
