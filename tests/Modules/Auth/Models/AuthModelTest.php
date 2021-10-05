<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-06
	 * Project: good-food-tracker-api
	 */

	namespace Modules\Auth\Models;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Entity\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotActiveException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use Gac\GoodFoodTracker\Modules\Auth\Models\AuthModel;
	use PHPUnit\Framework\TestCase;

	class AuthModelTest extends TestCase
	{

		public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
			parent::__construct($name, $data, $dataName);
			Database::execute_query("DELETE FROM users.user WHERE username='test'");
		}


		/** @test */
		public function register_account_success() {
			$password = '937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244';
			$user = AuthModel::register("Tester Test", "test@test.com", "test", $password);
			$this->assertInstanceOf(UserEntity::class, $user);
			$this->assertNotEmpty($user->id);
		}

		/** @test */
		public function registration_fails_because_username_is_taken() {
			$this->expectException(UsernameTakenException::class);
			$password = "'937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244'";
			AuthModel::register('Tester Test', 'test@test.com', 'test', $password);
		}

		/** @test */
		public function login_fails_when_invalid_credentials_provided() {
			$this->expectException(UserNotFoundException::class);
			$_REQUEST['username'] = 'test';
			$_REQUEST['password'] = 'test';
			AuthModel::login("test", "test");
			$this->fail('Should have thrown an exception');
		}

		/** @test */
		public function login_fails_when_account_is_not_active() {
			$this->expectException(UserNotActiveException::class);
			AuthModel::login("test", "937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244");
		}

		/** @test */
		public function login_success() {
			Database::execute_query("UPDATE users.user SET status = '1' WHERE username = 'test'");
			$_REQUEST['username'] = 'test';
			$_REQUEST['password'] = '937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244';

			$result = AuthModel::login('test', '937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244');
			$this->assertInstanceOf(UserEntity::class, $result["user"]);
			$this->assertNotEmpty($result["tokens"]["accessToken"]);
		}
	}
