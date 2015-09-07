<?php
namespace LBChat\Command\Server;

use LBChat\ChatClient;
use LBChat\ChatServer;

class IdentifyCommand extends Command implements IServerCommand {
	const TYPE_ALREADYONLINE = 1;
	const TYPE_INVAILD = 2;
	const TYPE_OUTOFDATE = 3;
	const TYPE_CHALLENGE = 4;
	const TYPE_SUCCESS = 5;

	protected $type;

	public function __construct(ChatServer $server, $type) {
		parent::__construct($server);
		$this->type = $type;
	}

	/**
	 * Execute a server command on a specific client. The command should not be modified.
	 *
	 * @param ChatClient $client The client on which to execute the server command
	 */
	public function execute(ChatClient $client) {
		switch ($this->type) {
		case self::TYPE_ALREADYONLINE:
			$client->send("IDENTIFY ALREADYONLINE");
			break;
		case self::TYPE_INVAILD:
			$client->send("IDENTIFY INVAILD");
			break;
		case self::TYPE_OUTOFDATE:
			$client->send("IDENTIFY OUTOFDATE");
			break;
		case self::TYPE_CHALLENGE:
			$client->send("IDENTIFY CHALLENGE");
			break;
		case self::TYPE_SUCCESS:
			$client->send("IDENTIFY SUCCESS");

			//Send them a LOGGED too
			$logged = new LoggedCommand($this->server);
			$logged->execute($client);

			break;
		}
	}
}