<?php
	/**
	 * Author: Igor Ilić <github@igorilic.net>
	 * Date: 2021-10-06
	 * Project: good-food-tracker-api
	 */

	namespace Modules\Auth\Controllers;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Modules\Auth\Controllers\AuthController;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UsernameTakenException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotActiveException;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use Gac\Routing\Request;
	use PHPUnit\Framework\TestCase;

	class AuthControllerTest extends TestCase
	{
		protected AuthController $controller;
		protected mixed          $requestMock;
		protected string         $username = "test";
		protected string         $password = "937E8D5FBB48BD4949536CD65B8D35C426B80D2F830C5C308E2CDEC422AE2244";

		public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
			parent::__construct($name, $data, $dataName);
			Database::execute_query("DELETE FROM users.user WHERE username='test'");
			$this->controller = new AuthController();

			$builder = $this->getMockBuilder(Request::class);
			$this->requestMock = $builder->getMock();
		}

		/** @test */
		public function register_account_success() {
			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"name" => "Tester Test",
						"email" => "test@test.com",
						"username" => "test",
						"password" => $this->password,
						"password_again" => $this->password,
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->register($this->requestMock);
			$this->assertEquals(1, 1);
		}

		/** @test */
		public function register_account_fails_because_of_missing_required_arguments() {
			$this->expectException(RequiredFieldException::class);
			$this->controller->register($this->requestMock);
			$this->fail("Should have thrown RequiredFieldException");
		}

		/** @test */
		public function registration_fails_because_username_is_taken() {
			$this->expectException(UsernameTakenException::class);

			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"name" => "Tester Test",
						"email" => "test@test.com",
						"username" => $this->username,
						"password" => $this->password,
						"password_again" => $this->password,
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->register($this->requestMock);
		}


		/** @test */
		public function login_fails_when_invalid_credentials_provided() {
			$this->expectException(UserNotFoundException::class);
			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"username" => $this->username,
						"password" => $this->password . "x",
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->login($this->requestMock);
			$this->fail('Should have thrown UserNotFoundException exception');
		}

		/** @test */
		public function login_fails_when_account_is_not_active() {
			$this->expectException(UserNotActiveException::class);

			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"username" => $this->username,
						"password" => $this->password,
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->login($this->requestMock);
			$this->fail('Should have thrown UserNotActiveException exception');
		}

		/** @test */
		public function login_success() {
			Database::execute_query("UPDATE users.user SET status = '1', activation_key = null WHERE username = 'test'");

			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"username" => $this->username,
						"password" => $this->password,
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->login($this->requestMock);
			$this->assertEquals(1, 1);
		}
	}
