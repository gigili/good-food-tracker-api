<?php

	namespace Core\Entities;

	use Gac\GoodFoodTracker\Entity\UserEntity;
	use PHPUnit\Framework\TestCase;

	class EntityTest extends TestCase
	{

		/** @test */
		public function save_method_returns_new_instance() {
			$mockClass = $this->getMockClass(UserEntity::class, [ "save" ]);
			$callMock = new $mockClass;

			$user = new UserEntity("Tester Test", "test@test.com", "test");

			$callMock->expects($this->once())
					 ->method("save")
					 ->will($this->returnValue($user));

			$this->assertEquals($user, $callMock->save());
		}

		/** @test */
		public function delete_method_gives_back_instance_of_deleted_item() {
			$mockClass = $this->getMockClass(UserEntity::class, [ "delete" ]);
			$callMock = new $mockClass;

			$user = new UserEntity("Tester Test", "test@test.com", "test");

			$callMock->expects($this->once())
					 ->method("delete")
					 ->will($this->returnValue($user));

			$this->assertEquals($user, $callMock->delete());
		}

		/** @test */
		public function get_method_gives_back_instance_of_searched_item() {
			$mockClass = $this->getMockClass(UserEntity::class, [ "get" ]);
			$callMock = new $mockClass;

			$user = new UserEntity("Tester Test", "test@test.com", "test");

			$callMock->expects($this->once())
					 ->method("get")
					 ->will($this->returnValue($user));

			$this->assertEquals($user, $callMock->get("1"));
		}

		/** @test */
		public function get_method_gives_back_empty_instance_when_invalid_value_provided() {
			$mockClass = $this->getMockClass(UserEntity::class, [ "get" ]);
			$callMock = new $mockClass;

			$user = new UserEntity();

			$callMock->expects($this->once())
					 ->method("get")
					 ->will($this->returnValue($user));

			$this->assertEquals($user, $callMock->get("9999"));
		}

		/** @test */
		public function filter_method_gives_back_a_list_of_accounts(){
			$mockClass = $this->getMockClass(UserEntity::class, [ "filter" ]);
			$callMock = new $mockClass;

			$user = new UserEntity();

			$callMock->expects($this->once())
					 ->method("filter")
					 ->will($this->returnValue([$user, $user, $user]));

			$this->assertEquals([$user, $user, $user], $callMock->filter(["name" => "gac"]));
		}
	}
