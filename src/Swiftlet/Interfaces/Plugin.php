<?php

namespace Swiftlet\Interfaces;

/**
 * Plugin interface
 */
interface Plugin extends Common
{
	/**
	 * Set application instance
	 * @param App $app
	 * @return Plugin
	 */
	public function setApp(App $app);

	/**
	 * Set controller instance
	 * @param Controller $controller
	 * @return Plugin
	 */
	public function setController(Controller $controller);

	/**
	 * Set view instance
	 * @param View $view
	 * @return Plugin
	 */
	public function setView(View $view);
}
