<?php namespace Swiftlet ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
	<head>
		<title><?php echo View::htmlEncode(Config::get('siteName')) . ' - ' . View::getTitle() ?></title>

		<link type="text/css" rel="stylesheet" href="<?php echo App::getRootPath() ?>views/css/layout.css"/>
	</head>
	<body>
