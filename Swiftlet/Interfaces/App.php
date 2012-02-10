<?php

namespace Swiftlet\Interfaces;

interface App
{
	public function __construct();

	public function serve();

	public function getAction();

	public function getArgs();

	public function getModel($modelName);

	public function getSingleton($modelName);

	public function getRootPath();

	public function registerHook($hookName, array $params = array());

	public function autoload($className);

	public function error($number, $string, $file, $line);
}
