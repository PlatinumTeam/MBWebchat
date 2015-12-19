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
		$this->time      = $time;
	}

	public function execute() {
		// Warning: if you use a negative mute, you should be using un-mute. Chances are you either made
		// a mistake and did a negative, or you were trying to un-mute someone. I'll be nice about it
		// and let the mod/admin know what to do.
		if ($this->time < 0) {
			$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this->client),
				"Cannot give a negative mute. Use /unmute <display name> to un-mute someone.");
			$chat->execute();

			return;
		}

		// If the person isn't muted yet, message them privately so we don't have a bunch of folks
		// bitch about why someone was muted. Don't make it public knowledge.
		if (!$this->recipient->isMuted()) {
			$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this->recipient),
				"You have been muted for spam/offensive chat.");
			$chat->execute();
		}

		// Add time on the recipient so that they can get the punishment that they deserve.
		$this->recipient->addMuteTime($this->time);

		// Message the administrator or moderator that the mute has successfully gone through.
		$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this->client),
			"You have successfully muted " . $this->recipient->getDisplayName() . ".");
		$chat->execute();
	}

	public static function init(ChatServer $server, ChatClient $client, $rest) {
		$words = String::getWordOptions($rest);

		switch (count($words)) {
		case 0:
			//Check your own mute
			$message = self::formatInfoMessage($client, true);
			return new WhisperCommand($server, ServerChatClient::getClient(), array($client), $message);
		case 1:
			//Check someone's mute time
			$user = array_shift($words);
			$recipient = $server->findClient($user);
			//Could not find them?
			if ($recipient === null) {
				return InvalidCommand::createUnknownUser($server, $client, $user);
			}

			$message = self::formatInfoMessage($recipient, false);
			return new WhisperCommand($server, ServerChatClient::getClient(), array($client), $message);
		case 2:
			//Mute someone for a time
			$user = array_shift($words);

			//Special case: use /muteall
			if ($user === "all") {
				$rest = substr($rest, 3); //strlen("all")
				return MuteAllCommand::init($server, $client, ltrim($rest));
			}

			$recipient = $server->findClient($user);
			//Could not find them?
			if ($recipient === null) {
				return InvalidCommand::createUnknownUser($server, $client, $user);
			}

			//Make sure we respect access levels
			if (!$client->checkPrivilege($recipient->getPrivilege())) {
				//You are not allowed to use this command
				return InvalidCommand::createAccessDenied($server, $client, "mute users with a greater privilege level");
			}

			$time = (int)array_shift($words);

			return new MuteCommand($server, $client, $recipient, $time);
		}
		return InvalidCommand::createUsage($server, $client, "/mute [, user [, time]]");
	}

	protected static function formatInfoMessage(ChatClient $client, $self) {
		$message = "";
		if ($self) $message .= "You are ";
		else       $message .= $client->getDisplayName() . " is ";

		if ($client->isMuted()) $message .= "muted for " . $client->getMuteTime() . " seconds.";
		else                    $message .= "unmuted.";

		return $message;
	}
}