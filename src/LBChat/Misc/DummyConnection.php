<?php
namespace LBChat\Misc;

use Ratchet\ConnectionInterface;

class DummyConnection implements ConnectionInterface {

	public $remoteAddress = "127.0.0.1";

	public function send($data) {
		//Dummy
	}
	public function close() {
		//Dummy
	}
}