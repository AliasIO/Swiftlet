<?php

namespace Swiftlet\Interfaces;

interface Model
{
	public function __construct(App $app, View $view, Controller $controller);
}
