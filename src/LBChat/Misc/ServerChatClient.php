<?php
namespace LBChat\Misc;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use Ratchet\ConnectionInterface;

class ServerChatClient extends ChatClient {

	/**
	 * @var ServerChatClient $client
	 */
	protected static $client;
	/**
	 * @var ConnectionInterface $dummy
	 */
	protected static $dummy;

	public static function create(ChatServer $server) {
		self::$dummy = new DummyConnection();
		self::$client = new ServerChatClient($server, self::$dummy);
	}

	public static function getClient() {
		return self::$client;
	}

	public static function getConnection() {
		return self::$dummy;
	}

	public static function sendMessage($global, ChatClient $recipient = null, $message) {
		self::getClient()->chat($global, $recipient, $message);
	}

	public function chat($global, ChatClient $recipient = null, $message) {
		$command = new ChatCommand($this->server, $this, $recipient, $message);

		if ($global || $recipient === null) {
			$this->server->broadcastCommand($command, $this);
		} else {
			$command->execute($recipient);
		}
	}

	public function onLogin() {
		//Don't call parent because this isn't a real client
	}

	public function onLogout() {
		//Don't call parent because this isn't a real client
	}

	public function getId() {
		return -1;
	}

	public function getUsername() {
		return "server";
	}

	public function getDisplayName() {
		return "SERVER";
	}

	public function getAccess() {
		return 1; //Moderator, but blue
	}

	public function getColor() {
		return "0000cc"; //Blue
	}

	public function getTitles() {
		return array("", "", "");
	}

	public function getVisible() {
		//Don't have them be on the list or accessible
		return false;
	}

	public function isMuted() {
		//Can't mute the server
		return false;
	}

	public function addMuteTime($time) {
		//Can't mute the server
	}

	public function cancelMute() {
		//Can't mute the server
	}
}