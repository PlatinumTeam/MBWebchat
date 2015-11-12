<?php
namespace LBChat\Integration;

use LBChat\Database\Database;

/**
 * Defines a set of methods for getting information about users from a database.
 * Interface IUserSupport
 * @package LBChat\Integration
 */
interface IUserSupport {
	/**
	 * @param Database $database A Database to use for queries
	 */
	public function __construct(Database $database);

	/**
	 * Get a user's ID based on their username
	 * @param string $username The user's username
	 * @return int
	 */
	public function getId($username);

	/**
	 * Resolve a user's username, generally just capitalization
	 * @param string $username The user's username
	 * @return string
	 */
	public function getUsername($username);

	/**
	 * Get a user's access level (0 = user, 1 = mod, 2 = admin)
	 * @param string $username The user's username
	 * @return int
	 */
	public function getAccess($username);

	/**
	 * Get a user's display name to be shown in the chat and list.
	 * @param string $username The user's username
	 * @return string
	 */
	public function getDisplayName($username);

	/**
	 * Get the user's custom username color in a "RRGGBB" (hex) form
	 * @param string $username The user's username
	 * @return string
	 */
	public function getColor($username);

	/**
	 * Get a user's various titles that decorate their name
	 * @param string $username The user's username
	 * @return array An array containing the flair, prefix, and suffix in that order.
	 */
	public function getTitles($username);

	/**
	 * Get a temporary username for a guest
	 * @return string The guest's username
	 */
	public function getGuestUsername();

	/**
	 * Determine if a user is a guest by their username
	 * @param string $username The username to check
	 * @return boolean If they're a guest
	 */
	public function isGuest($username);

	/**
	 * Check if a user is banned from the site
	 * @param string $username The username to check
	 * @param string $address The user's IP address
	 * @return boolean If that user is banned
	 */
	public function isBanned($username, $address);

	/**
	 * Attempt to login a user.
	 * @param string $username The user's username
	 * @param string $type Either "key" or "password" for which method to use
	 * @param string $data The key/password to use
	 * @return boolean If the login succeeded
	 */
	public function tryLogin($username, $type, $data);

	/**
	 * Add a friend for a user
	 * @param string $username The user's username
	 * @param string $friend The friend's username
	 */
	public function addFriend($username, $friend);

	/**
	 * Add a user's friend
	 * @param string $username The user's username
	 * @param string $friend The friend's username
	 */
	public function removeFriend($username, $friend);

	/**
	 * Get a user's friend list
	 * @param string $username The user's username
	 * @return array The user's friend list
	 */
	public function getFriendList($username);
}