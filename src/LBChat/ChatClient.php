<?php
namespace LBChat;
use Guzzle\Http\Message\EntityEnclosingRequest;
use Guzzle\Http\Message\Header;
use LBChat\Command\Chat\WhisperCommand;
use LBChat\Command\Server\AcceptTOSCommand;
use LBChat\Command\Server\IdentifyCommand;
use LBChat\Command\Server\InvalidCommand;
use LBChat\Command\Server\NotifyCommand;
use LBChat\Filter\CapsFilter;
use LBChat\Filter\FilterGroup;
use LBChat\Filter\ChatFilter;
use LBChat\Filter\ProfanityFilter;
use LBChat\Integration\IUserSupport;
use LBChat\Misc\ServerChatClient;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoConnection;
use Ratchet\WebSocket\Version\Hixie76;
use Ratchet\WebSocket\Version\RFC6455;
use React\Socket\Connection;

/**
 * A basic chat client class that outlines how clients behave.
 * Class ChatClient
 * @package LBChat
 */
class ChatClient {
	protected $server;
	protected $connection;
	protected $support;
	private $username;
	private $display;
	private $location;
	private $access;
	private $color;
	private $titles;
	private $muted;
	private $muteTime;
	private $visible;
	protected $loggedIn;
	protected $guest;
	private $friends;
	protected $acceptedTOS;
	protected $filter;

	public function __construct(ChatServer $server, ConnectionInterface $connection, IUserSupport $support) {
		$this->server = $server;
		$this->connection = $connection;
		$this->support = $support;
		$this->username = "";
		$this->display = "";
		$this->location = 0;
		$this->access = 0;
		$this->color = "000000";
		$this->titles = array("", "", "");
		$this->muted = false;
		$this->muteTime = 0;
		$this->visible = true;
		$this->loggedIn = false;
		$this->guest = false;
		$this->friends = array();
		$this->acceptedTOS = false;
		$this->filter = null;
	}

	/**
	 * Interpret a raw message from the client connection.
	 * @param string $msg The message
	 */
	public function interpretMessage($msg) {
		$command = Command\CommandFactory::construct($this->server, $this, $msg);

		if ($command === null) {
			$command = new InvalidCommand($this->server);
			$command->execute($this);
		} else {
			$command->execute();
		}
	}

	/**
	 * Send a raw message out to the client connection
	 * @param string $msg The message
	 */
	public function send($msg) {
		$this->connection->send($msg);
	}

	/**
	 * Disconnect the client from the server, closing all connections
	 */
	public function disconnect() {
		$this->connection->close();
	}

	/**
	 * Callback for when the client has successfully logged in
	 * @return boolean If the login was successful
	 */
	public function onLogin() {
		//If we're banned, don't let us on
		if ($this->support->isBanned($this->getUsername(), $this->getAddress())) {
			//Let us
			$command = new IdentifyCommand($this->server, IdentifyCommand::TYPE_BANNED);
			$command->execute($this);
			//Close our connection
			$this->disconnect();
			return false;
		}

		//Check if they need to accept the TOS
		if (!$this->getAcceptedTOS()) {
			//They do need to accept the TOS
			$command = new AcceptTOSCommand($this->server);
			$command->execute($this);

			//Don't disconnect them, but don't let them in until they accept
			return false;
		}

		$privilege = $this->getPrivilege();
		switch ($privilege) {
		case 0:
			$this->setChatFilter(new FilterGroup($this->server, $this, array(new CapsFilter($this->server, $this),
			                                                                 new ProfanityFilter($this->server, $this))));
			break;
		case 1:
			$this->setChatFilter(new FilterGroup($this->server, $this, array(new CapsFilter($this->server, $this),
			                                                                 new ProfanityFilter($this->server, $this))));
			break;
		case 2:
			$this->setChatFilter(new FilterGroup($this->server, $this, array(new CapsFilter($this->server, $this),
			                                                                 new ProfanityFilter($this->server, $this))));
			break;
		}

		$this->loggedIn = true;
		return true;
	}

	/**
	 * Callback for when the client logs out, before disconnecting
	 */
	public function onLogout() {
		if ($this->loggedIn) {
			$this->server->sendAllUserlists();
			$this->server->broadcastCommand(new NotifyCommand($this->server, $this, "logout", - 1, $this->location), $this);
		}
	}

	/**
	 * Compare one client to another
	 * @param ChatClient $other The other client
	 * @return bool If the two clients are equal
	 */
	public function compare(ChatClient $other) {
		return $other->connection === $this->connection;
	}

	/**
	 * Get the user's server
	 * @return ChatServer The server
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * Get the user's support connection
	 * @return IUserSupport The support
	 */
	public function getSupport() {
		return $this->support;
	}

	/**
	 * Get the client's user Id
	 * @return int The client's user ID
	 */
	public function getId() {
		//No database access in this class, just use the resourceId on the connection
		return $this->connection->resourceId;
	}

	/**
	 * Get the client's IP address
	 * @return string The client's IP address
	 */
	public function getAddress() {
		//Check if we're proxying through stunnel.
		if ($this->connection->remoteAddress === "127.0.0.1") {
			if ($this->connection instanceof RFC6455\Connection || $this->connection instanceof Hixie76\Connection) {
				/* @var RFC6455\Connection|Hixie76\Connection $this ->connection */
				$request = $this->connection->WebSocket->request;
				/* @var EntityEnclosingRequest $request */
				if ($request->hasHeader("X-Forwarded-For")) {
					//It's a forwarded connection
					/* @var Header $header */
					$header = $request->getHeader("X-Forwarded-For");

					//Make sure it exists
					if ($header->count() > 0) {
						return $header->toArray()[0];
					}
				}
			}
		}
		return $this->connection->remoteAddress;
	}

	/**
	 * Get the client's username
	 * @return string The client's username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set the client's username
	 * @param string $username The new username
	 */
	public function setUsername($username) {
		$this->username = $username;
		$this->display = $username;
	}

	/**
	 * Get if the client is a guest
	 * @return bool If the client is a guest
	 */
	public function isGuest() {
		return $this->guest;
	}


	/**
	 * Set the client to have a guest's username
	 */
	public function setGuest() {
		$this->setUsername("Guest");
		$this->guest = true;
		$this->login("guest", "guest");
	}

	/**
	 * Get the client's display name.
	 * @return string The client's display name
	 */
	public function getDisplayName() {
		return $this->display;
	}

	/**
	 * Set the client's display name
	 * @param string $display The new display name
	 */
	public function setDisplayName($display) {
		$this->display = $display;
	}

	/**
	 * Get the client's Location.
	 * @return int The client's location
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * Set the client's location
	 * @param int $location The new location
	 */
	public function setLocation($location) {
		$this->location = $location;
		$this->server->sendAllUserlists();
	}

	/**
	 * Get the client's access.
	 * @return int The client's access
	 */
	public function getAccess() {
		return $this->access;
	}

	/**
	 * Set the client's Access
	 * @param int $access The new access
	 */
	public function setAccess($access) {
		$this->access = $access;
	}

	/**
	 * Get the client's privilege level (different from access as Guests are 0 instead of 3)
	 * @return int The client's privilege level
	 */
	public function getPrivilege() {
		switch ($this->getAccess()) {
		case -3: return 0;
		case 3:  return 0;
		default: return $this->getAccess();
		}
	}

	/**
	 * Get the client's color
	 * @return string The client's color
	 */
	public function getColor() {
		return $this->color;
	}

	/**
	 * Set the client's color
	 * @param string $color The client's color
	 */
	public function setColor($color) {
		$this->color = $color;
	}

	/**
	 * Get the client's titles
	 * @return array The client's titles
	 */
	public function getTitles() {
		return $this->titles;
	}

	/**
	 * Set the client's titles
	 * @param array $titles The client's titles
	 */
	public function setTitles($titles) {
		$this->titles = $titles;
	}

	/**
	 * Set a specific title on a client
	 * @param int $index The index of the title to set
	 * @param string $title The title which will be set
	 */
	public function setTitle($index, $title) {
		$this->titles[$index] = $title;
	}

	/**
	 * Get if the client should be displayed on user lists and accessible via commands.
	 * @return bool If the client is visible
	 */
	public function getVisible() {
		return $this->visible;
	}

	/**
	 * Set whether the client should be displayed on user lists and accessible via commands.
	 * @param bool $visible If the client should be displayed
	 */
	public function setVisible($visible) {
		$this->visible = $visible;
	}

	/**
	 * Get if the client is logged in
	 * @return bool If the client is logged in
	 */
	public function getLoggedIn() {
		return $this->loggedIn;
	}

	/**
	 * Set if the client is logged in
	 * @param bool $loggedIn If the client should be logged in
	 */
	public function setLoggedIn($loggedIn) {
		$this->loggedIn = $loggedIn;
	}

	/**
	 * Perform a login on the client.
	 * @param string $type The type of login, either "key" or "password"
	 * @param string $data The key/password to use for verification, depending on what is used in $type.
	 */
	public function login($type, $data) {
		if ($this->tryLogin($type, $data)) {
			//Login succeeded
			if ($this->onLogin()) {
				if ($this->server->onClientLogin($this)) {
					$command = new IdentifyCommand($this->server, IdentifyCommand::TYPE_SUCCESS);
					$command->execute($this);
				} else {
					//Server rejected our login. Oh well
					$command = new IdentifyCommand($this->server, IdentifyCommand::TYPE_INVAILD);
					$command->execute($this);
					$this->disconnect();
				}
			}
		} else {
			//Login failed
			$command = new IdentifyCommand($this->server, IdentifyCommand::TYPE_INVAILD);
			$command->execute($this);
			$this->disconnect();
		}
	}

	/**
	 * Attempt to verify login details
	 * @param string $type The type of login, either "key" or "password"
	 * @param string $data The key/password to use for verification, depending on what is used in $type.
	 * @return bool If the login was successful
	 */
	public function tryLogin($type, $data) {
		//Base users have no login conditions
		return true;
	}

	/**
	 * Determine if the client has accepted the TOS
	 * @return bool If the client accepted the TOS
	 */
	public function getAcceptedTOS() {
		return $this->acceptedTOS;
	}

	/**
	 * Set if this client has accepted the TOS
	 * @param bool $accepted If the client has accepted the TOS
	 */
	public function setAcceptedTOS($accepted) {
		$this->acceptedTOS = $accepted;
	}

	/**
	 * Called once every second.
	 */
	public function onSecondAdvance() {
		if ($this->muted) {
			$this->muteTime--;
			if ($this->muteTime <= 0) {
				$this->cancelMute();
			}
		}
	}

	/**
	 * Get if the client is muted
	 * @return bool If the client is muted
	 */
	public function isMuted() {
		return $this->muted;
	}

	/**
	 * Get for how long the client is muted
	 * @return int The length of the client's mute, in seconds
	 */
	public function getMuteTime() {
		return $this->muteTime;
	}

	/**
	 * Mute the client for a specified amount of time, adding to their current mute time.
	 * @param int $time The time (in seconds) to add
	 */
	public function addMuteTime($time) {
		$this->muteTime += $time;
		$this->muted = true;
	}

	/**
	 * Completely cancel/terminate a client's mute
	 */
	public function cancelMute() {
		$this->muteTime = 0;
		$this->muted = false;
		$chat = new WhisperCommand($this->server, ServerChatClient::getClient(), array($this), "You have been unmuted.");
		$chat->execute();
	}

	/**
	 * Add a friend for a client
	 * @param string $friend The friend's username
	 */
	public function addFriend($friend) {
		if (!in_array($friend, $this->friends))
			$this->friends[] = $friend;
	}

	/**
	 * Remove a client's friend
	 * @param string $friend The friend's username
	 */
	public function removeFriend($friend) {
		if (in_array($friend, $this->friends)) {
			//Find the position of the friend
			$position = array_search($friend, $this->friends);
			//Splice the object out from the middle of the array
			array_splice($this->friends, $position, 1);
		}
	}

	/**
	 * Get the user's list of friends
	 * @return array The user's friend list
	 */
	public function getFriendList() {
		//Map each friend name to a username / display pair
		return array_map(function ($friend) {
			return array("username" => $friend, "display" => $friend);
		}, $this->friends);
	}

	/**
	 * Check if a client is allowed to perform an action based on privilege levels
	 * @param int $level The command's privilege level
	 * @return bool If the client can use this command
	 */
	public function checkPrivilege($level) {
		return $this->getPrivilege() >= $level;
	}

	/**
	 * Get the user's current chat filter
	 * @return ChatFilter
	 */
	public function getChatFilter() {
		return $this->filter;
	}

	/**
	 * Set the user's chat filter
	 * @param ChatFilter $filter The new filter to use
	 */
	public function setChatFilter(ChatFilter $filter) {
		$this->filter = $filter;
	}
}
