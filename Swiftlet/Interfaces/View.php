<?php

namespace Swiftlet\Interfaces;

interface View
{
	public function __construct(\Swiftlet\App $app, $name);

	public static function getName();

	public static function setName($name);

	public static function get($variable, $htmlEncode = true);

	public static function set($variable, $value = null);

	public static function htmlEncode($value);

	public static function htmlDecode($value);

	public static function render();
}
