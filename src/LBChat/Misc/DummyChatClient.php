<?php
namespace LBChat\Misc;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\ChatCommand;
use Ratchet\ConnectionInterface;

/**
 * A simple subclass of the basic client that can send messages from a user who is offline
 * Class DummyChatClient
 * @package LBChat\Misc
 */
class DummyChatClient extends ChatClient {

	/**
	 * @var ConnectionInterface $dummy
	 */
	protected $dummy;

	/**
	 * Create a new dummy chat client
	 * @param ChatServer $server The chat server
	 * @param string $username The dummy client's username
	 * @param int    $access   The dummy client's access
	 */
	public function __construct(ChatServer $server, $username, $access) {
		$this->dummy = new DummyConnection();
		parent::__construct($server, $this->dummy, $server->getUserSupport());
		$this->setUsername($username);
		$this->setAccess($access);
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
	
	public function getDisplayName() {
		return $this->getUsername();
	}

	public function getVisible() {
		//Don't have them be on the list or accessible
		return false;
	}
}