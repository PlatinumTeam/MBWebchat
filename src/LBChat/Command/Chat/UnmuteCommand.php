<?php

namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Misc\ServerChatClient;
use LBChat\Utils\String;

class UnmuteCommand extends Command implements IChatCommand {
    protected $recipient;

    public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient = null) {
        parent::__construct($server, $client);

        $this->recipient = $recipient;
    }

    public function execute() {
        // broadcast message so everyone knows.
        $message = "[col:1][b]" . $this->recipient->getDisplayName() . " has been unmuted by " . $this->client->getDisplayName() . ".";
        $chat = new ChatCommand($this->server, ServerChatClient::getClient(), null, $message);
        $this->server->broadcastCommand($chat);        

        // Cancel the mute
        $this->recipient->cancelMute();
    }

    public static function init(ChatServer $server, ChatClient $client, $rest) {
        $words = String::getWordOptions($rest);

	    //Correct usage is like this
	    if (count($words) != 1) {
		    return InvalidCommand::createUsage($server, $client, "/unmute <user>");
	    }

        $user = array_shift($words);
        $recipient = $server->findClient($user);

	    //Couldn't find them
        if ($recipient === null) {
            return InvalidCommand::createUnknownUser($server, $client, $user);
        }
	    // If the person is already unmuted, why are you muting them.
	    if ($recipient->isMuted()) {
		    return InvalidCommand::createGeneric($server, $client, "User is not muted.");
	    }

        return new UnmuteCommand($server, $client, $recipient);
    }
}