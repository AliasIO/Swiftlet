<?php

namespace Swiftlet\Interfaces;

/**
 * Plugin interface
 */
interface Plugin
{
	/**
	 * Constructor
	 * @param App $app
	 * @param View $view
	 * @param Controller $controller
	 */
	public function __construct(App $app, View $view, Controller $controller);
}
