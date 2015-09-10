<?php

namespace LBChat\Command\Chat;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use LBChat\Misc\ServerChatClient;
use LBChat\Utils\String;

class MuteCommand extends Command implements IChatCommand {
	protected $recipient;
	protected $time;

	public function __construct(ChatServer $server, ChatClient $client, ChatClient $recipient = null, $time) {
		parent::__construct($server, $client);

		$this->recipient = $recipient;
		$this->time = $time;
	}

	public function execute() {
		// Warning: if you use a negative mute, you should be using un-mute. Chances are you either made
		// a mistake and did a negative, or you were trying to un-mute someone. I'll be nice about it
		// and let the mod/admin know what to do.
		if ($this->time < 0) {
			$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), $this->client, "Cannot give a negative mute. Use /unmute <display name> to un-mute someone.");
			$chat->execute();
			return;
		}

		// If the person isn't muted yet, we will embarrass them by display they have been muted.
		if (!$this->recipient->isMuted()) {
			$message = "[col:1][b]" . $this->recipient->getDisplayName() . " has been muted by " . $this->client->getDisplayName() . ".";
			$chat = new ChatCommand($this->server, ServerChatClient::getClient(), null, $message);
			$this->server->broadcastCommand($chat);
		}

		// Add time on the recipient so that they can get the punishment that they deserve.
		$this->recipient->addMuteTime($this->time);
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$recipient = $server->findClient(String::decodeSpaces(array_shift($words)));
		if ($recipient === null)
			return null;
		return new MuteCommand($server, $client, $recipient, $words[0]);
		$words = String::getWordOptions($rest);
	}
}