<?php

class Form_Button extends Form_Input
{
	public function __construct($name, $title, $link = null)
	{
		// If we have a link; we're actually an <a class='btn'>
		if (isset($link)) {
			$this->setAttribute('href', htmlspecialchars($link));
			$this->addClass('btn-default');
			$type = null;
		} else {
			$this->addClass('btn-primary');
			$type = 'submit';
		}

		parent::__construct($name, $title, $type);

		$this->removeClass('form-control')->addClass('btn');
	}

	protected function _getInput()
	{
		if (empty($this->getAttribute('href'))) {
			return parent::_getInput();
		}

		$element = preg_replace('~^<input(.*)/>$~', 'a\1', parent::_getInput());

		return <<<EOT
	<{$element}>{$this->_title}</a>
EOT;
	}
}
