# MagickMethods Behavior for CakePHP 2.x

## Introduction

This provides magick methods more powerful than CakePHP's.
Magick method is exactly slow but it is very suitable to develop rapidly.

### Summary for this behavior:

 - find{Any} can be called. findHoge() is valid if you propery defined the custom find method.
 - You can get only find query contains conditions.
 - Callback method can be used what you created and built-in methods.

## Installation

in your plugins directory,

	git clone git://github.com/hiromi2424/magick_methods.git

or in current directory of your repository,

	git submodule add git://github.com/hiromi2424/magick_methods.git plugins/magick_methods


## Usage

### Loading

in AppModel or your specific model:

	public $actsAs = array(
		/* ... */
		'MagickMethods.MagickMethods',
	);

or

	$YourModel->Behaviors->load('MagickMethods.MagickMethods');

### Using methods

You can use magick methods as like past syntax, but query must be array:

	$YourModel->findById($id, array('order' => 'name'));
	/*
		same as:
		$YourModel->find('first', array('conditions' => array('YourModel.id' => $id), 'order' => 'name'));
	*/

You can also get criteria:

	$this->paginate = $this->YourModel->scopeDisabled(false);
	/*
		returns:
		array(
			'conditions' => array(
				'disabled' => false
			)
		)
	*/

I don't recommend heavily complicated magick but can be used:

	$yourModel->findAllByEnabledOrBannedAndAdminOrRegisterYear(true, false, true, 2011);
	/*
		same as:
		$YourModel->find('all', array('conditions' => array(
			array(
				'OR' => array(
					'YourModel.enabled' => true,
					'YourModel.banned' => false,
				),
			),
			array(
				'OR' => array(
					'YourModel.admin' => true,
					'YourModel.register_year' => 2011,
				),
			),
		)));
	*/

You can create and use callback methods:

#### Create

	// note: method name is case-sensitive.
	public function byLike() {

		$scope = array();
		foreach ($this->data[$this->alias] as $field => $value) {
			$scope[$this->escapeField($field) . ' like'] = "%$value%";
		}

		return $scope;

	}

#### Use

	$YourModel->findAllByLike();

	// built-in callback
	$YourModel->findByInsetId();

## License

Licensed under The MIT License.
Redistributions of files must retain the above copyright notice.


Copyright 2011 hiromi, https://github.com/hiromi2424

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.