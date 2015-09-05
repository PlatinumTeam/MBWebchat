<?php
namespace LBChat\Database;

use LBChat\ChatServer;

class SQLChatServer extends ChatServer {
	protected $database;

	public function __construct($database) {
		parent::__construct();

		$this->database = $database;
	}

}