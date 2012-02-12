<?php

namespace Swiftlet\Interfaces;

interface View
{
	public function __construct(App $app, $name);

	public function get($variable, $htmlEncode = true);

	public function set($variable, $value = null);

	public function htmlEncode($value);

	public function htmlDecode($value);

	public function render();
}
