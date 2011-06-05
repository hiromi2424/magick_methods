<?php

class MagickMethodsBehavior extends ModelBehavior {

	public $mapMethods = array();

	protected $_findMap = array('/^find(.+)$/' => '_findMagick');
	protected $_scopeMap = array('/^scope(.+)$/' => '_scopeMagick');

	public static $defaultSettings = array(
		'callbackPrefix' => 'by',
		'associations' => array(
			'hasOne',
			'belongsTo',
			'hasMany',
			'hasAndBelongsToMany',
		),
	);

	public function setup($Model, $config = array()) {
		$this->mapMethods = array_merge($this->_findMap, $this->_scopeMap);
		$this->settings[$Model->alias] = Set::merge(self::$defaultSettings, $config);
	}

	public function _findMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		preg_match(key($this->_findMap) , $method, $matched);
		list($type, $parts) = $this->_parse($matched[1]);

		list($fields, $operators) = $this->_extract($parts);

		$query = empty($fields) ? array() : $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));

		return $Model->find($type, $query);

	}

	public function _scopeMagick($Model) {

		$args = func_get_args();
		/* $Model = */ array_shift($args);
		$method = array_shift($args);
		$args = array_values($args);

		preg_match(key($this->_scopeMap) , $method, $matched);

		list($fields, $operators) = $this->_extract($matched[1]);

		$query = $this->_findParams(compact('Model', 'fields', 'operators', 'args', 'method'));

		return $query;

	}

	protected function _parse($matched) {

		$parts = null;
		if (strpos($matched, 'By') !== false) {
			list($type, $parts) = explode('By', $matched);
		} else {
			$type = $matched;
		}

		if (empty($type)) {
			$type = 'first';
		}

		return array(strtolower($type), $parts);

	}

	protected function _extract($parts) {

		if (empty($parts)) {
			return array(array(), array());
		}

		$parts = Inflector::underscore($parts);
		$parts = explode('_', $parts);

		$elements = $this->_extractElements($parts);

		return $elements;

	}

	protected function _extractElements($parts) {

		$fields = array();
		$operators = array();

		while ($element = current($parts)) {

			if (in_array($element, array('and', 'or'))) {

				$operators[] = $element;
				next($parts);

			} else {

				$field = array();
				do {

					$field[] = $element;
					$element = next($parts);

				} while ($element && !in_array($element, array('and', 'or')));

				$fields[] = implode('_', $field);

			}

		}

		if (count($fields) -1 !== count($operators)) {
			throw new MagickMethods_IllegalMethodNameException("Trailing 'or', 'and' is not expected");
		} elseif (count(array_unique($fields)) !== count($fields)) {
			throw new MagickMethods_IllegalMethodNameException('Scope name must be used at once');
		}

		return array($fields, $operators);

	}

	protected function _findParams($params) {
		extract($params);

		$scopes = array();
		$offset = 0;
		foreach ($fields as $field) {

			$Field = Inflector::camelize($field);
			$callback = $this->settings[$Model->alias]['callbackPrefix'] . $Field;

			if ($Model->hasMethod($callback)) {

				$scopes = Set::merge($scopes, $Model->$callback());

			} else {

				if (!isset($args[$offset])) {
					throw new MagickMethods_MissingArgumentException(array($offset + 1, $method));
				}

				$value = $args[$offset];
				$offset++;

				if ($Model->hasField($field, true) || !($assocField = $this->_retrieveAssociatedField($Model, $field))) {
					$scopes[$Model->escapeField($field)] = $value;
				} else {
					$scopes[$assocField] = $value;
				}

			}

		}
		$query = isset($args[$offset]) ? $args[$offset] : array();

		if (empty($operators) || !in_array('or', $operators)) {
			$criteria = $scopes;
		} else {
			$criteria = $this->_generatesOrCreteria($scopes, $operators);
		}

		return Set::merge(array('conditions' => $criteria), $query);

	}

	protected function _retrieveAssociatedField($Model, $field) {
		$parts = explode('_', $field);
		$partsCount = count($parts);

		foreach ($Model->getAssociated() as $alias => $assoc) {

			if (in_array($assoc, $this->settings[$Model->alias]['associations'])) {
				for ($i = 1; $i <= $partsCount; $i++) {
					$search = Inflector::camelize(implode('_', array_slice($parts, 0, $i)));

					if ($alias === $search) {
						$field = implode('_', array_slice($parts, $i));
						if ($Model->$alias->hasField($field, true)) {
							return $Model->$alias->escapeField($field);
						}
					}
				}
			}

		}

		return false;
	}

	protected function _generatesOrCreteria($scopes, $operators) {

		$criteria = $scopeParts = $parts = array();

		$operator = current($operators);
		foreach ($scopes as $field => $value) {

			$parts = array_merge($parts, array($field => $value));

			if ($operator === 'and') {
				$scopeParts[] = $parts;
				$parts = array();
			}

			$operator = next($operators);

		}

		if (!empty($parts)) {
			$scopeParts[] = $parts;
		}

		foreach ($scopeParts as $parts) {

			if (count($parts) > 1) {
				$criteria[] = array('OR' => $parts);
			} else {
				$criteria[key($parts)] = current($parts);
			}

		}

		return $criteria;

	}

	public function byInsertId($Model) {
		return array($Model->escapeField($Model->primaryKey) => $Model->getInsertId());
	}
}

class MagickMethodsException extends CakeException {

	function __construct($message = array(), $code = 500) {

		if ($this->_messageTemplate) {
			$message = __d('magick_methods', $this->_messageTemplate, $message);
		} else {
			$message = __d('magick_methods', $message);
		}

	}

}

class MagickMethods_MissingArgumentException extends MagickMethodsException {

	protected $_messageTemplate = 'Missing argument %d for %s';

}

class MagickMethods_IllegalMethodNameException extends MagickMethodsException {
	protected $_messageTemplate = '';

}

