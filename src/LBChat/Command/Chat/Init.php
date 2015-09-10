<?php
namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\ChatCommandFactory;
use LBChat\Command\CommandFactory;

function _addCommands($array) {
	foreach ($array as $class) {
		if (is_array($class)) {
			_addCommand($class[0], $class[1], $class[2]);
		} else {
			_addCommand($class);
		}
	}
}

function _addCommand($name, $class = null, $caseSensitive = false) {
	if ($class === null)
		$class = "LBChat\\Command\\Chat\\{$name}Command";

	ChatCommandFactory::addCommandType($name, function(ChatServer $server, ChatClient $client, $rest, $msg) use ($class) {
		return call_user_func(array($class, "init"), $server, $client, $rest, $msg);
	}, $caseSensitive);
}

//Add all the commands
_addCommands(
	array(
		//    command     class name (with path)
		array("!PQ",      "LBChat\\Command\\Chat\\PQCommand",      true ),
		array("PQ WHERe", "LBChat\\Command\\Chat\\PQCommand",      true ),
		array("/whisper", "LBChat\\Command\\Chat\\WhisperCommand", false),
		array("/send",    "LBChat\\Command\\Chat\\SendCommand",    false),
		array("/mute",    "LBChat\\Command\\Chat\\MuteCommand",    false),
		array("/muteall", "LBChat\\Command\\Chat\\MuteAllCommand", false),
		array("/unmute",  "LBChat\\Command\\Chat\\UnmuteCommand",  false),
		array("/stop",    "LBChat\\Command\\Chat\\StopCommand",    false),
		array("/ping",    "LBChat\\Command\\Chat\\PingCommand",    false),


		//Keep this line at the bottom to catch all invalid commands. No /command will work if it comes after this line.
		array("/",        "LBChat\\Command\\Chat\\InvalidCommand", false)
	)
);
