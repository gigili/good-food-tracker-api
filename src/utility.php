<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-09-06
	 * Project: Good Food Tracker - API
	 */

	use Firebase\JWT\BeforeValidException;
	use Firebase\JWT\ExpiredException;
	use Firebase\JWT\JWT;
	use Firebase\JWT\SignatureInvalidException;
	use Gac\GoodFoodTracker\Core\Exceptions\InvalidTokenException;
	use Gac\GoodFoodTracker\Core\Exceptions\MissingTokenException;
	use JetBrains\PhpStorm\ArrayShape;
	use Predis\Client as RedisClient;

	if ( !function_exists('generate_token') ) {
		/**
		 * Method used to generate JWT tokens
		 *
		 * @param string $userID ID of the user for whom the token is being generated
		 * @param bool $generateRefreshToken Indicated if the refresh token should be generated and returned as well
		 * @param bool $forceTokenRegeneration
		 *
		 * @return array<string,string|null> Returns an array containing the access token and refresh token if $generateRefreshToken is set to True values
		 */
		#[ArrayShape( [ 'accessToken' => 'string', 'refreshToken' => 'null|string' ] )] function generate_token(
			string $userID,
			bool   $generateRefreshToken = false,
			bool   $forceTokenRegeneration = false
		) : array {
			$currentTime = time();

			$redis = new RedisClient([
				'host' => $_ENV['REDIS_HOST'],
				'port' => $_ENV['REDIS_PORT'],
			]);

			$payload = [
				'iss' => $_SERVER['HTTP_HOST'],
				'aud' => $_SERVER['HTTP_HOST'],
				'iat' => $currentTime,
				'nbf' => $currentTime,
				'jti' => $userID,
			];

			$accessToken = $redis->get("{access_token:$userID}");
			$refreshToken = $generateRefreshToken ? $redis->get("{refresh_token:$userID}") : NULL;

			if ( is_null($accessToken) || $forceTokenRegeneration ) {
				$accessTokenPayload = $payload;
				$accessTokenPayload['exp'] = strtotime(date('Y-m-d H:i:s', strtotime(' + 2 hours')));
				$accessToken = JWT::encode($accessTokenPayload, $_ENV['JWT_KEY']);
				$redis->set("{access_token:$userID}", $accessToken);
				$redis->expireat("{access_token:$userID}", $accessTokenPayload['exp']);
			}

			if ( $generateRefreshToken === true && is_null($refreshToken) ) {
				$refreshToken = JWT::encode($payload, $_ENV['JWT_KEY']);
				$redis->set("{refresh_token:$userID}", $refreshToken);
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
		 * @throws InvalidTokenException
		 * @throws MissingTokenException
		 * @throws InvalidArgumentException     Provided JWT was empty
		 * @throws UnexpectedValueException     Provided JWT was invalid
		 * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
		 * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
		 * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
		 * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
		 */
		function decode_token() : void {
			if ( !isset($_SERVER['HTTP_AUTHORIZATION']) ) throw new MissingTokenException();

			$matches = [];
			if ( !preg_match('/Bearer Request =>|Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches) ) {
				throw new MissingTokenException();
			}

			$token = $matches[1] ?? "";
			$decoded = JWT::decode($token, $_ENV['JWT_KEY'], [ 'HS256' ]);
			$userID = $decoded->jti ?? NULL;

			if ( is_null($userID) ) throw new InvalidTokenException();
			$_SESSION['userID'] = $userID;
		}
	}