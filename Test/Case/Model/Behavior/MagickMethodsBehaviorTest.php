<?php

App::uses('MagickMethodsBehaviorMockModel', 'MagickMethods.TestSuite/Mock');

class MagickMethodsBehaviorTest extends CakeTestCase {

	public function setup() {
		$this->Model = new MagickMethodsBehaviorMockModel;
	}

	public function tearDown() {
		unset($this->Model);
		ClassRegistry::flush();
	}

	public function testNormalConditions() {

		$result = $this->Model->findById(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findAllById(1);
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findAllById(array(1, 2));
		$expected = array('all', array('conditions' => array(
			$this->Model->escapeField('id') => array(1, 2),
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByIdAndUserNameAndPassword(1, 'john', 'I love you');
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 1,
			$this->Model->escapeField('user_name') => 'john',
			$this->Model->escapeField('password') => 'I love you',
		)));
		$this->assertEqual($expected, $result);

	}

	public function testOrConditions() {

		$result = $this->Model->findByIdOrUserId(1, 2);
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			)
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByIdOrUserIdOrName(1, 2, 'john');
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				$this->Model->escapeField('name') => 'john',
				),
			)
		)));
		$this->assertEqual($expected, $result);

	}

	public function testComplicatedConditions() {

		$result = $this->Model->findByIdOrUserIdAndName(1, 2, 'john');
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			),
			$this->Model->escapeField('name') => 'john',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByNameAndIdOrUserId('john', 1, 2);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('name') => 'john',
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			),
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByAgeOrNameAndIdOrUserId(12, 'john', 1, 2);
		$expected = array('first', array('conditions' => array(
			array(
				'OR' => array(
					$this->Model->escapeField('age') => 12,
					$this->Model->escapeField('name') => 'john',
				),
			),
			array(
				'OR' => array(
					$this->Model->escapeField('id') => 1,
					$this->Model->escapeField('user_id') => 2,
				),
			)
		)));
		$this->assertEqual($expected, $result);

	}

	public function testCallbacks() {

		$result = $this->Model->findByInsertId();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserIdAndInsertId(1);
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('user_id') => 1,
			$this->Model->escapeField('id') => 2,
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByUserDefined();
		$expected = array('first', array('conditions' => array(
			$this->Model->escapeField('specific_field') => 'value!',
		)));
		$this->assertEqual($expected, $result);

		$result = $this->Model->findByMultiParameters();
		$expected = array('first', array('conditions' => array(
			'hoge' => 'piyo',
			'fuga' => 'moge',
			'untara' => 1,
		)));
		$this->assertEqual($expected, $result);

	}

	public function testNoScope() {

		$result = $this->Model->findCount();
		$expected = array('count', array());
		$this->assertEqual($expected, $result);

		$result = $this->Model->findUserFindType();
		$expected = array('userfindtype', array());
		$this->assertEqual($expected, $result);

	}

	public function testScope() {

		$result = $this->Model->scopeId(1);
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 1,
		));
		$this->assertEqual($expected, $result);

		$result = $this->Model->scopeInsertId();
		$expected = array('conditions' => array(
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEqual($expected, $result);

		$result = $this->Model->scopeEnabledAndInsertId(true);
		$expected = array('conditions' => array(
			$this->Model->escapeField('enabled') => true,
			$this->Model->escapeField('id') => 2,
		));
		$this->assertEqual($expected, $result);

	}

	public function testErrors() {

		try {
			$this->Model->findById();
			$this->fail('Expected MagickMethods_MissingArgumentException was not thrown');
		} catch (MagickMethodsException $e) {
			$this->assertInstanceOf('MagickMethods_MissingArgumentException', $e);
		}

		try {
			$this->Model->findByIdAndId(1, 2);
			$this->fail('Expected MagickMethods_IllegalMethodNameException was not thrown');
		} catch (MagickMethodsException $e) {
			$this->assertInstanceOf('MagickMethods_IllegalMethodNameException', $e);
		}

		try {
			$this->Model->findByIdOr(1);
			$this->fail('Expected MagickMethods_IllegalMethodNameException was not thrown');
		} catch (MagickMethodsException $e) {
			$this->assertInstanceOf('MagickMethods_IllegalMethodNameException', $e);
		}

	}

}