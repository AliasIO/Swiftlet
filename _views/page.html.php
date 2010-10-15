<div class="no-grid">
	<?php if ( !empty($view->error) ): ?>
	<h1><?php echo $view->pageTitle ?></h1>

	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( isset($view->body) ): ?>
	<?php if ( $app->permission->check('admin page edit') || $app->permission->check('admin page delete') ): ?>
	<ul class="admin-toolbox">
		<?php if ( $app->permission->check('admin page edit') ): ?>
		<li><a class="button" href="<?php echo $view->route('admin/page/edit/' . $view->nodeId) ?>"><?php echo $view->t('Edit') ?></a></li>
		<?php endif ?>
	</ul>
	<?php endif ?>

	<?php if ( $view->parents ): ?>
	<ul class="crumbs">
		<?php foreach ( $view->parents as $path => $title ): ?>
		<li><a href="<?php echo $view->route($path) ?>"><?php echo $title ?></a> &rsaquo;</li>
		<?php endforeach ?>
		<li><?php echo $view->pageTitle ?></li>
	</ul>
	<?php endif ?>

	<h1><?php echo $view->pageTitle ?></h1>

	<?php echo $view->body ?>
	<?php endif ?>
</div>
