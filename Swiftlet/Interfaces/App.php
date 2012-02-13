<?php

namespace Swiftlet\Interfaces;

interface App
{
	public function run();

	public function serve();

	public function getConfig($variable);

	public function setConfig($variable, $value);

	public function getRootPath();

	public function getAction();

	public function getArgs();

	public function getModel($modelName);

	public function getSingleton($modelName);

	public function registerHook($hookName, array $params = array());

	public function autoload($className);

	public function error($number, $string, $file, $line);
}
