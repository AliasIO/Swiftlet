<?php

namespace Swiftlet\Interfaces;

interface Plugin
{
	public function __construct(\Swiftlet\App $app, \Swiftlet\View $view, \Swiftlet\Controller $controller);
}
