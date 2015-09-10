<?php
/**
 * Created by PhpStorm.
 * User: Jeff Hutchinson
 * Date: 9/6/2015
 * Time: 8:29 PM
 */

namespace LBChat\Command\Chat;


use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Misc\ServerChatClient;
use LBChat\Utils\String;

class MuteAllCommand extends Command implements IChatCommand {
    protected $time;

    public function __construct(ChatServer $server, ChatClient $client, $time) {
        parent::__construct($server, $client);
        $this->time = $time;
    }

    public function execute() {
        // Send a message saying that everyone has been muted.
        $message = "[col:1][b]" . $this->client->getDisplayName() . " has muted everyone.";
        $chat = new ChatCommand($this->server, ServerChatClient::getClient(), null, $message);
        $this->server->broadcastCommand($chat);

        // get all the clients
        $clients = $this->server->getAllClients();

        /* @var ChatClient $client */
        foreach ($clients as $client) {
            // administrators and moderators aren't gonna get muted :)
            if ($client->getPrivilege() == 0) {
                // you're a pleb and deserved to be muted
                // add mute time to the client.
                $client->addMuteTime($this->time);
            }
        }
    }

    public static function init(ChatServer $server, ChatClient $client, $rest) {
        $words = String::getWordOptions($rest);
	    if (count($words) != 1) {
		    return InvalidCommand::createUsage($server, $client, "/muteall <time>");
	    }

	    $time = (int)array_shift($words);
        return new MuteAllCommand($server, $client, $time);
    }
}