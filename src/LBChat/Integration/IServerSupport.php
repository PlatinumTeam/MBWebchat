<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

/**
 * Defines a set of methods for getting server information
 * Interface IServerSupport
 * @package LBChat\Integration
 */
interface IServerSupport {
	/**
	 * Get the server preference with the given key
	 * @param string $key The key for the preference
	 * @return string The value for the preference
	 */
	public function getPreference($key);

	/**
	 * Get an array of number => name for different location statuses
	 * @return array The status list
	 */
	public function getStatusList();

	/**
	 * Get an array of identified => color for the chat colors
	 * @return array The color list
	 */
	public function getColorList();

	/**
	 * Get the welcome message to be displayed for users in chat
	 * @param bool $webchat If the user is using webchat
	 * @return string The welcome message
	 */
	public function getWelcomeMessage($webchat = false);
}