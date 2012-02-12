<?php

namespace Swiftlet\Interfaces;

interface Plugin
{
	public function __construct(App $app, View $view, Controller $controller);
}
