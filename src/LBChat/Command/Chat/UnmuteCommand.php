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
        // You must specify who you are going to mute!
        if ($this->recipient == null) {
            $chat = new WhisperCommand($this->server, ServerChatClient::getClient(), $this->client, "Usage: /unmute
            <name>");
            $chat->execute();
            return;
        }

        // If the person is already unmuted, why are you muting them.
        if (!$this->recipient->isMuted()) {
            $chat = new WhisperCommand($this->server, ServerChatClient::getClient(), $this->client, "Already unmuted.");
            $chat->execute();
            return;
        }

        // broadcast message so everyone knows.
        $message = "[col:1][b]" . $this->recipient->getDisplayName() . " has been unmuted by " . $this->client->getDisplayName() . ".";
        $chat = new ChatCommand($this->server, ServerChatClient::getClient(), null, $message);
        $this->server->broadcastCommand($chat);        

        // Cancel the mute
        $this->recipient->cancelMute();
    }

    public static function init(ChatServer $server, ChatClient $client, $rest) {
        $words = explode(" ", $rest);
        $recipient = $server->findClient(String::decodeSpaces(array_shift($words)));
        if ($recipient === null)
            return null;
        return new UnmuteCommand($server, $client, $recipient);
    }
}