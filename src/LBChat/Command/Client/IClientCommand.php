<?php
namespace LBChat\Command\Client;

interface IClientCommand {
	/**
	 * Execute the given client command, applying any changes that it represents.
	 */
	public function execute();
}
