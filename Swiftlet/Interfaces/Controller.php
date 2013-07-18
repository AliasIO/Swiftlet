<?php

namespace Swiftlet\Interfaces;

/**
 * Controller interface
 */
interface Controller
{
	/**
	 * Constructor
	 * @param App $app
	 * @param View $view
	 */
	public function __construct(App $app, View $view);

	/**
	 * Default action
	 */
	public function index();
}
