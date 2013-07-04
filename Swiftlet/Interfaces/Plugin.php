<?php

namespace Swiftlet\Interfaces;

/**
 * Plugin interface
 */
interface Plugin
{
	public function __construct(App $app, View $view, Controller $controller);
}
