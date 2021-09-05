<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-05
	 * Project: Good Food Tracker - API
	 */

	namespace Gac\GoodFoodTracker\Modules\Auth;


	use Firebase\JWT\JWT;

	/**
	 * Class Token
	 *
	 * @package Gac\GoodFoodTracker\Modules\Auth
	 */
	class Token
	{
		protected string $accessToken;

		protected ?string $refreshToken = NULL;

		/**
		 * Get the value of access token
		 *
		 * @return string Return the value of access token
		 */
		public function getAccessToken() : string {
			return $this->accessToken;
		}

		/**
		 * Set the value of access token
		 *
		 * @param string $accessToken
		 */
		public function setAccessToken(string $accessToken) : void {
			$this->accessToken = $accessToken;
		}

		/**
		 * Get the value of refresh token
		 *
		 * @return string|null
		 */
		public function getRefreshToken() : ?string {
			return $this->refreshToken;
		}

		/**
		 * Set the value of refresh token
		 *
		 * @param string|null $refreshToken Returns the value of refresh token
		 */
		public function setRefreshToken(?string $refreshToken) : void {
			$this->refreshToken = $refreshToken;
		}

		/**
		 * Check if access token is still valid
		 *
		 * @return bool Returns true if the token is still valid otherwise false
		 */
		public function is_access_token_valid() : bool {
			$data = JWT::decode($this->accessToken, $_ENV['JWT_KEY'], array( 'HS256' ));
			return ( $data["exp"] > strtotime(date("Y-m-d H:i:s")) );
		}

		/**
		 * Used to invalidate both access and refresh tokens
		 */
		public function invalidate_tokens() {
			//TODO: create this method
		}

		/**
		 * How the object should be serialized
		 *
		 * @return object Returns an object containing both access and refresh tokens
		 */
		public function __serialize() {
			return (object) [
				'access_token' => $this->accessToken,
				'refresh_token' => $this->refreshToken,
			];
		}
	}