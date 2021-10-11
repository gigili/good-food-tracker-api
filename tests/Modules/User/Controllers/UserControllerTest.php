<?php

	namespace Modules\User\Controllers;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\RequiredFieldException;
	use Gac\GoodFoodTracker\Entity\UserEntity;
	use Gac\GoodFoodTracker\Modules\Auth\Exceptions\UserNotFoundException;
	use Gac\GoodFoodTracker\Modules\User\Controllers\UserController;
	use Gac\Routing\Request;
	use PHPUnit\Framework\TestCase;

	class UserControllerTest extends TestCase
	{
		protected UserController $controller;
		protected mixed          $requestMock;

		public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
			parent::__construct($name, $data, $dataName);

			$this->controller = new UserController();
			$builder = $this->getMockBuilder(Request::class);
			$this->requestMock = $builder->getMock();
		}

		/** @test */
		public function get_users() {
			$this->requestMock
				->method("get")
				->will($this->returnCallback(function ($param) {
					$params = [
						"usernameOrEmail" => "test",
					];

					return $params[$param] ?? NULL;
				}));


			$this->requestMock
				->method("send")
				->will($this->returnCallback(function ($params) {
					$this->assertCount(1, $params["data"]);
				}));

			$this->controller->get_users($this->requestMock);
		}

		/** @test */
		public function exception_gets_thrown_for_invalid_user_id() {
			$this->expectException(InvalidUUIDException::class);
			$this->controller->get_user($this->requestMock, "aaaa");
			$this->fail("Should have thrown InvalidUUIDException exception");
		}

		/** @test */
		public function exception_gets_thrown_for_user_not_found() {
			$this->expectException(UserNotFoundException::class);
			$this->controller->get_user($this->requestMock, "26a1322d-7cbc-430a-abb1-9c081f156a72");
			$this->fail("Should have thrown UserNotFoundException exception");
		}

		/** @test */
		public function get_user() {
			$user = Database::execute_query("SELECT * FROM users.user WHERE username = 'test'", [], true);

			$this->requestMock
				->method('send')
				->will($this->returnCallback(function ($params) {
					$this->assertInstanceOf(UserEntity::class, $params['data']);
				}));

			$this->controller->get_user($this->requestMock, $user->id);
		}

		/** @test */
		public function update_user_account_throws_exception_for_missing_data() {
			$this->expectException(RequiredFieldException::class);
			$this->controller->update_user_account($this->requestMock);
		}

		/** @test */
		public function update_user_account_fails_if_no_session_user_id_present() {
			$this->expectException(UserNotFoundException::class);
			$this->requestMock
				->method('get')
				->will($this->returnCallback(function ($param) {
					$params = [
						'name' => 'Tester Test',
						'email' => "test@test.com",
					];

					return $params[$param] ?? NULL;
				}));

			$this->controller->update_user_account($this->requestMock);
		}

		/** @test */
		public function update_user_account() {
			$user = Database::execute_query("SELECT * FROM users.user WHERE username = 'test'", [], true);
			$_SESSION["userID"] = $user->id;

			$this->requestMock
				->method('get')
				->will($this->returnCallback(function ($param) {
					$params = [
						'name' => 'Tester Test',
						'email' => "test@test.com",
					];

					return $params[$param] ?? NULL;
				}));

			$this->requestMock
				->method('send')
				->will($this->returnCallback(function ($params) {
					$this->assertInstanceOf(UserEntity::class, $params['data']);
				}));

			$this->controller->update_user_account($this->requestMock);
		}

		/** @test */
		public function delete_user_account() {
			$user = Database::execute_query("SELECT * FROM users.user WHERE username = 'test'", [], true);
			$_SESSION['userID'] = $user->id;

			$this->requestMock
				->method('send')
				->will($this->returnCallback(function ($params) {
					$this->assertEquals("Account deleted successfully", $params['message']);
				}));

			$this->controller->delete_user_account($this->requestMock);
		}
	}
