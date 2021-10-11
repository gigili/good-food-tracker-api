<?php

	namespace Modules\User\Controllers;

	use Gac\GoodFoodTracker\Core\DB\Database;
	use Gac\GoodFoodTracker\Core\Exceptions\Validation\InvalidUUIDException;
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

			$this->controller->get_users($this->requestMock);
			$this->assertEquals(1, 1);
		}

		/** @test */
		public function exception_gets_thrown_for_invalid_user_id(){
			$this->expectException(InvalidUUIDException::class);
			$this->controller->get_user($this->requestMock, "aaaa");
			$this->fail("Should have thrown an exception");
		}

		/** @test */
		public function exception_gets_thrown_for_user_not_found(){
			$this->expectException(UserNotFoundException::class);
			$this->controller->get_user($this->requestMock, "26a1322d-7cbc-430a-abb1-9c081f156a72");
			$this->fail("Should have thrown an exception");
		}

		/** @test */
		public function get_user() {
			$res = Database::execute_query("SELECT * FROM users.user WHERE username = 'test'", [], true);
			$this->controller->get_user($this->requestMock, $res->id);
			$this->assertEquals(1,1);
		}

		/** @test */
		public function update_user_account() {
			$this->assertEquals(1, 3);
		}

		/** @test */
		public function delete_user_account() {
			$this->assertEquals(1, 3);
		}
	}
