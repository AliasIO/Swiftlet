<?php

namespace Swiftlet\Interfaces;

interface Controller
{
	public function __construct(\Swiftlet\App $app, \Swiftlet\View $view);

	public function index();

	public function notImplemented();
}
