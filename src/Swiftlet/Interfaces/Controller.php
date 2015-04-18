<?php

namespace Swiftlet\Interfaces;

/**
 * Controller interface
 */
interface Controller extends Common
{
	/**
	 * Set application instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Controller
	 */
	public function setApp(App $app);

	/**
	 * Set view instance
	 * @param Interfaces\App $app
	 * @return Interfaces\Controller
	 */
	public function setView(View $view);

	/**
	 * Set page title
	 * @param string $app
	 * @return Interfaces\Controller
	 */
	public function setTitle($title);

	/**
	 * Get routes
	 * @return array
	 */
	public function getRoutes();

	/**
	 * Default action
	 */
	public function index();
}
