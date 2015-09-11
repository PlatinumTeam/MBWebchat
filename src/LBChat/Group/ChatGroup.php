<?php
namespace LBChat\Group;

use LBChat\ChatClient;
use LBChat\ChatServer;
use LBChat\Command\Server\GroupCommand;
use LBChat\Command\Server\IServerCommand;
use LBChat\Misc\ServerChatClient;

class ChatGroup {
	/**
	 * @var ChatServer $server
	 */
	protected $server;
	/**
	 * @var string $name
	 */
	protected $name;
	/**
	 * @var \SplObjectStorage $clients
	 */
	protected $clients;

	public function __construct(ChatServer $server, $name) {
		$this->server = $server;
		$this->name = $name;
		$this->clients = new \SplObjectStorage();

		//Always have the server chat in the group
		$this->addClient(ServerChatClient::getClient());
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function addClient(ChatClient $client) {
		//Let the group know that someone is joining
		$command = new GroupCommand($this->server, GroupCommand::ACTION_LOGIN, $client);
		$this->broadcastCommand($command);

		$this->clients->attach($client);
		$client->joinGroup($this);

		$command = new GroupCommand($this->server, GroupCommand::ACTION_JOIN, $this);
		$command->execute($client);
	}

	public function removeClient(ChatClient $client) {
		$command = new GroupCommand($this->server, GroupCommand::ACTION_LEAVE, $this);
		$command->execute($client);

		$this->clients->detach($client);
		$client->leaveGroup($this);

		//Let the group know that they left
		$command = new GroupCommand($this->server, GroupCommand::ACTION_LOGOUT, $client);
		$this->broadcastCommand($command);
	}

	public function broadcast($message, ChatClient $exclude = null) {
		foreach ($this->clients as $client) {
			/* @var ChatClient $client */

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$client->send($message);
		}
	}

	public function broadcastCommand(IServerCommand $command, ChatClient $exclude = null) {
		echo("Group {$this->name} broadcasting a command.\n");
		foreach ($this->clients as $client) {
			/* @var ChatClient $client */

			if ($exclude !== null && $client->compare($exclude))
				continue;

			$command->execute($client);
		}
	}

	/**
	 * Get all of the clients in the server
	 * @return \SplObjectStorage
	 */
	public function getAllClients() {
		return $this->clients;
	}
}