<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
	<head>
		<title><?php echo self::htmlEncode(Swiftlet\Config::get('siteName')) . ' - ' . self::get('pageTitle') ?></title>

		<link type="text/css" rel="stylesheet" href="<?php echo self::$_app->getRootPath() ?>views/css/layout.css"/>
	</head>
	<body>
