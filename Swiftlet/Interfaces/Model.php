<?php

namespace Swiftlet\Interfaces;

interface Model
{
	public function __construct(\Swiftlet\App $app, \Swiftlet\View $view, \Swiftlet\Controller $controller);
}
