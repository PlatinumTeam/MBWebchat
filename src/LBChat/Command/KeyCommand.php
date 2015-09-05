<?php
namespace LBChat\Command;

class KeyCommand extends Command {
	protected $key;

	public function __construct($client, $key) {
		parent::__construct($client);
		$this->key = $key;
	}

	public function parse() {
		$this->client->login("key", $this->key);
	}
}