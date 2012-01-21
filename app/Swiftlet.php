<?php

class Swiftlet
{
	const
		version = '3.0'
		;

	protected
		$_action     = 'indexAction',
		$_args       = array(),
		$_controller,
		$_rootPath   = '/',
		$_singletons = array(),
		$_view
		;

	/**
	 *
	 */
	public function __construct()
	{
		set_error_handler(array($this, 'error'), E_ALL);

		// Determine the root path
		if ( !empty($_SERVER['SERVER_NAME']) && !empty($_SERVER['REQUEST_URI']) ) {
			$requestUri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);

			$this->_rootPath = preg_replace('/^' . preg_quote($_SERVER['SERVER_NAME'], '/') . '/', '', $requestUri);
		}

		// Extract controller name, view name, action name and arguments from URL
		$controllerName = 'IndexController';
		$viewName       = 'index';

		if ( !empty($_GET['q']) ) {
			$this->_args = explode('/', $_GET['q']);

			if ( $this->_args ) {
				$viewName = array_shift($this->_args);

				$controllerName = ucfirst($viewName) . 'Controller';
			}

			if ( $this->_args ) $this->_action = array_shift($this->_args)  . 'Action';
		}

		if ( !is_file('controllers/' . $controllerName . '.php') ) {
			$controllerName = 'Error404Controller';
			$viewName       = 'error404';
		}

		// Instantiate the view
		$this->_view = new SwiftletView($this, $viewName);

		// Instantiate the controller
		require('controllers/' . $controllerName . '.php');

		$this->_controller = new $controllerName($this, $controllerName);

		// Call the controller action
		if ( !method_exists($this->_controller, $this->_action) ) {
			$this->_action = 'notImplementedAction';
		}

		$this->_controller->{$this->_action}();

		// Render the view
		$this->_view->render();
	}

	/**
	 * Get the action name
	 * @return string
	 */
	public function getAction()
   	{
		return $this->_action();
	}

	/**
	 * Get a model
	 * @param string $modelName
	 * @return object
	 */
	public function getModel($modelName)
   	{
		$modelName = ucfirst($modelName) . 'Model';

		if ( is_file($file = 'models/' . $modelName . '.php') ) {
			// Instantiate the model
			require($file);

			return new $modelName($this, $modelName);
		} else {
			throw new Exception($modelName . ' not found');
		}
	}

	/**
	 * Get a model singleton
	 * @param string $modelName
	 * @return object
	 */
	public function getSingleton($modelName)
	{
		if ( isset($this->_singletons[$modelName]) ) {
			return $this->_singletons[$modelName];
		}

		$model = $this->getModel($modelName);

		$this->_singletons[$modelName] = $model;

		return $model;
	}

	/**
	 * Get the controller instance
	 * @return object
	 */
	public function getController()
   	{
		return $this->_controller;
	}

	/**
	 * Get the view instance
	 * @return object
	 */
	public function getView()
   	{
		return $this->_view;
	}

	/**
	 * Get the client-side absolute path to root
	 * @return object
	 */
	public function getRootPath()
	{
		return $this->_rootPath;
	}

	/**
	 * Error handler
	 * @param int $number
	 * @param string $string
	 * @param string $file
	 * @param int $line
	 */
	public function error($number, $string, $file, $line)
	{
		throw new Exception('Error #' . $number . ': ' . $string . ' in ' . $file . ' on line ' . $line);
	}
}
