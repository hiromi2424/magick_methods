<?php

App::uses('Model', 'Model');

class MagickMethodsBehaviorMockModel extends Model {

	public $useTable = false;
	public $actsAs = array('MagickMethods.MagickMethods');

	public function find() {
		$args = func_get_args();
		return $args;
	}

	public function getInsertId() {
		return 2;
	}

	public function byUserDefined() {
		return array($this->escapeField('specific_field') => 'value!');
	}

	public function byMultiParameters() {
		return array(
			'hoge' => 'piyo',
			'fuga' => 'moge',
			'untara' => 1,
		);
	}
}
