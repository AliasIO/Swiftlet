<?php

class SwiftletView
{
	protected
		$_app,
		$_name,
		$_variables = array()
		;

	/**
	 * Initialize the view
	 * @param object $app
	 * @param string $name
	 */
	public function __construct($app, $name)
	{
		$this->_app  = $app;
		$this->_name = $name;
	}

	/**
	 * Get the view name
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the page title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->htmlEncode($this->_app->getController()->getTitle());
	}

	/**
	 * Get a view variable
	 * @params string $variable
	 * @return string
	 */
	public function get($variable)
   	{
		if ( isset($this->_variables[$variable]) ) {
			return $this->htmlEncode($this->_variables[$variable]);
		}
	}

	/**
	 * Set a view variable
	 * @param string $variable
	 * @param mixed $value
	 */
	public function set($variable, $value = null)
	{
		$this->_variables[$variable] = $value;
	}

	/**
	 * Render the view
	 */
	public function render()
	{
		if ( is_file($file = 'views/' . $this->_name . '.html.php') ) {
			header('X-Generator: Swiftlet ' . Swiftlet::version);

			require($file);
		} else {
			throw new Exception('View not found');
		}
	}

	/**
	 * Make a string safe for HTML
	 * @param string $string
	 * @return string
	 */
	protected function htmlEncode($string)
   	{
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}
}
