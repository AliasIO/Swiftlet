<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
	<head>
		<title><?php echo $view->siteName ?> - <?php echo t($view->pageTitle) ?></title>

		<link type="text/css"  rel="stylesheet"    href="<?php echo $view->rootPath ?>css.php?file=global"/>

		<link type="image/png" rel="shortcut icon" href="<?php echo $view->rootPath ?>favicon.ico"/>

		<meta http-equiv="content-type"     content="text/html; charset=UTF-8"/>
		<meta http-equiv="content-language" content="en-US"/>

		<meta name="title"        content="<?php echo $view->siteName ?> - <?php echo t($view->pageTitle) ?>"/>
		<meta name="distribution" content="global"/>
		<meta name="generator"    content="Swiftlet - http://swiftlet.org"/>
		<meta name="copyright"    content="<?php echo $view->siteCopyright   ?>"/>
		<meta name="designer"     content="<?php echo $view->siteDesigner    ?>"/>
		<meta name="description"  content="<?php echo $view->pageDescription ?>"/>
		<meta name="keywords"     content="<?php echo $view->pageKeywords    ?>"/>

		<?php if ( $contr->inAdmin ): ?>
		<script type="text/javascript" src="<?php echo $view->rootPath ?>lib/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="<?php echo $view->viewPath ?>scripts/ckeditor_conf.js.php"></script>
		<?php endif ?>
		
		<script type="text/javascript" src="<?php echo $view->rootPath ?>lib/jquery/jquery-1.4.2.min.js"></script>
	</head>
	<body class="<?php echo $contr->inAdmin ? 'in-admin' : '' ?>">
		<div id="header">
			<h1 id="logo">
				<a href="<?php echo $view->rootPath ?>" title="<?php echo t('Home') ?>"><?php echo $view->siteName ?></a>
			</h1>

			<ul class="header-links">
				<?php if ( isset($model->user) ): ?>

				<?php if ( !empty($model->perm->ready) && $model->perm->check('admin access') ): ?>
				<li>
					<a href="<?php echo $view->rootPath ?>admin/"><?php echo t('Dashboard') ?></a>
				</li>
				<?php endif ?>
				
				<?php if ( $model->session->get('user id') != user::guestId ): ?>
				<li>
					<a href="<?php echo $view->rootPath ?>account/"><?php echo t('Account', $model->session->get('user username')) ?></a>
				</li>
				<li>
					<a href="<?php echo $view->rootPath ?>login/?logout"><?php echo t('Logout') ?></a>
				</li>
				<?php else: ?>
				<li>
					<a href="<?php echo $view->rootPath ?>login/"><?php echo t('Login') ?></a>
				</li>
				<?php endif; ?>
				
				<?php endif; ?>
			</ul>
			
			<div style="clear: both;"></div>
		</div>

		<div id="page">
			<div id="content">
				<?php if ( $contr->inAdmin ): ?>
				<p id="admin-bar">
					<?php echo t('Administration') ?>
				</p>
				<?php endif ?>
