<?php

declare(strict_types=1);

namespace Swiftlet\Interfaces;

/**
 * Controller interface
 */
interface Controller extends Common
{
	/**
	 * Constructor
	 * @param App $app
	 * @param View $view
	 * @return Controller
	 */
	public function __construct(App $app, View $view);

	/**
	 * Set page title
	 * @param string $app
	 * @return Interfaces\Controller
	 */
	public function setTitle(string $title): Controller;

	/**
	 * Get routes
	 * @return array
	 */
	public function getRoutes(): array;

	/**
	 * Default action
	 */
	public function index();
}
