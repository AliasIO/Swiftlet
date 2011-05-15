<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
	<head>
		<title><?php echo $this->siteName . ( $this->pageTitle ? ' - ' : '' ) . $this->t($this->pageTitle) ?></title>

		<link type="text/css"  rel="stylesheet"    href="<?php echo $this->viewPath ?>global.css"/>
		<link type="text/css"  rel="stylesheet"    href="<?php echo $this->viewPath ?>grid.css"  />

		<link type="image/png" rel="shortcut icon" href="<?php echo $this->rootPath ?>favicon.ico"/>

		<meta http-equiv="content-type"     content="text/html; charset=UTF-8"/>
		<meta http-equiv="content-language" content="en-US"/>

		<meta name="title"        content="<?php echo $this->siteName . ( $this->pageTitle ? ' - ' : '' ) . $this->t($this->pageTitle) ?>"/>
		<meta name="distribution" content="global"/>
		<meta name="generator"    content="Swiftlet - http://swiftlet.org"/>
		<meta name="copyright"    content="<?php echo $this->siteCopyright   ?>"/>
		<meta name="designer"     content="<?php echo $this->siteDesigner    ?>"/>
		<meta name="description"  content="<?php echo $this->pageDescription ?>"/>
		<meta name="keywords"     content="<?php echo $this->pageKeywords    ?>"/>

		<?php if ( $controller->inAdmin ): ?>
		<script type="text/javascript" src="<?php echo $this->viewPath ?>scripts/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="<?php echo $this->route('ckeditor') ?>"></script>
		<?php endif ?>

		<script type="text/javascript" src="<?php echo $this->viewPath ?>scripts/jquery/jquery-1.6.1.min.js"></script>
	</head>
	<body class="<?php echo $controller->inAdmin ? 'in-admin' : '' ?>">
		<div id="header">
			<h1 id="logo">
				<a href="<?php echo $this->rootPath ?>" title="<?php echo $this->t('Home') ?>"><?php echo $this->siteName ?></a>
			</h1>

			<div id="menu">
				<ul>
					<li>
						<a href="<?php echo $this->rootPath ?>"><?php echo $this->t('Home') ?></a>
					</li>
					<?php if ( !empty($app->header->menu) ): ?>
					<?php foreach ( $app->header->menu as $item => $path ): ?>
					<li>
						<a href="<?php echo $path ?>"><?php echo $this->t($item) ?></a>
					</li>
					<?php endforeach ?>
					<?php endif ?>
				</ul>

				<?php if ( $controller->inAdmin ): ?>
				<p>
					<?php echo $this->t('Administration') ?>
				</p>
				<?php endif ?>
			</div>

			<div style="clear: both;"></div>
		</div>

		<div id="page">
			<div id="content">
