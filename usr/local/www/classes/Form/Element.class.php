<?php

class Form_Element
{
	protected $_attributes = array('class' => array());
	protected $_parent;

	public function addClass()
	{
		foreach (func_get_args() as $class) {
			$this->_attributes['class'][$class] = true;
		}

		return $this;
	}

	public function removeClass($class)
	{
		unset($this->_attributes['class'][$class]);

		return $this;
	}

	public function getClasses()
	{
		return implode(' ', array_keys($this->getAttribute('class')));
	}

	public function setAttribute($key, $value = null)
	{
		$this->_attributes[$key] = $value;

		return $this;
	}

	public function getAttribute($name)
	{
		return $this->_attributes[$name];
	}

	public function removeAttribute($name)
	{
		unset($this->_attributes[$name]);

		return $this;
	}

	public function getHtmlAttribute()
	{
		/* Will overwright _attributes['class'] with string Therefore you cannot 
		 * delete or add classes once getHtmlAttribute() has been called. */
		if (empty($this->_attributes['class'])) {
			$this->removeAttribute('class');
		} else {
			$this->_attributes['class'] = $this->getClasses();
		}

		$attributes = '';
		foreach ($this->_attributes as $key => $value) {
			$attributes .= ' ' . $key . (isset($value) ? '="' . htmlspecialchars($value) . '"' : '');
		}

		return $attributes;
	}

	protected function _setParent(Form_Element $parent)
	{
		$this->_parent = $parent;
	}
}
