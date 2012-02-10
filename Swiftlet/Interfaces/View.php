<?php

namespace Swiftlet\Interfaces;

interface View
{
	public function __construct(\Swiftlet\App $app, $name);

	public function getName();

	public function setName($name);

	public function get($variable, $htmlEncode = true);

	public function set($variable, $value = null);

	public function htmlEncode($value);

	public function htmlDecode($value);

	public function render();
}
