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
		//    command     class name (with path)
		array("/pq",      "LBChat\\Command\\Chat\\PQCommand"),
		array("!pq",      "LBChat\\Command\\Chat\\PQCommand"),
		array("/whisper", "LBChat\\Command\\Chat\\WhisperCommand"),
		array("/send",    "LBChat\\Command\\Chat\\SendCommand"),
		array("/mute",    "LBChat\\Command\\Chat\\MuteCommand"),
		array("/muteall", "LBChat\\Command\\Chat\\MuteAllCommand"),
		array("/unmute",  "LBChat\\Command\\Chat\\UnmuteCommand"),
		array("/stop",    "LBChat\\Command\\Chat\\StopCommand"),
		array("/ping",    "LBChat\\Command\\Chat\\PingCommand")
	)
);
