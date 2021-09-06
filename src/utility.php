<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-06
	 * Project: Good Food Tracker - API
	 */

	use Firebase\JWT\JWT;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidTokenException;
	use Gac\GoodFoodTracker\Core\Exceptions\MissingTokenException;
	use JetBrains\PhpStorm\ArrayShape;

	if ( !function_exists('generate_token') ) {
		/**
		 * Method used to generate JWT tokens
		 *
		 * @param string $userID ID of the user for whom the token is being generated
		 * @param bool $generateRefreshToken Indicated if the refresh token should be generated and returned as well
		 *
		 * @return array Returns an array containing the access token and refresh token if $generateRefreshToken is set to True values
		 */
		#[ArrayShape( [ 'accessToken' => 'string', 'refreshToken' => 'null|string' ] )] function generate_token(
			string $userID,
			bool $generateRefreshToken = false
		) : array {
			$currentTime = time();

			$payload = array(
				'iss' => $_SERVER['HTTP_HOST'],
				'aud' => $_SERVER['HTTP_HOST'],
				'iat' => $currentTime,
				'nbf' => $currentTime,
				'jti' => $userID,
			);

			$accessTokenPayload = $payload;
			$accessTokenPayload['exp'] = strtotime(date('Y-m-d H:i:s', strtotime(' + 2 hours')));
			$accessToken = JWT::encode($accessTokenPayload, $_ENV['JWT_KEY']);
			$refreshToken = NULL;

			if ( $generateRefreshToken === true ) {
				$refreshToken = JWT::encode($payload, $_ENV['JWT_KEY']);
			}

			return [
				'accessToken' => $accessToken,
				'refreshToken' => $refreshToken,
			];
		}
	}

	if ( !function_exists('decode_token') ) {
		/**
		 * Method used to decode a JWT token and extract and save to the session value of a user ID
		 *
		 * @throws MissingTokenException
		 * @throws InvalidTokenException
		 */
		function decode_token() {
			if ( !isset($_SERVER['HTTP_AUTHORIZATION']) ) throw new MissingTokenException();

			if ( !preg_match('/Bearer Request =>|Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches) ) {
				throw new MissingTokenException();
			}

			$token = $matches[1];
			$decoded = JWT::decode($token, $_ENV['JWT_KEY'], array( 'HS256' ));
			$userID = $decoded->jti;

			if ( is_null($userID) ) throw new InvalidTokenException();

			$_SESSION['userID'] = $userID;
		}
	}