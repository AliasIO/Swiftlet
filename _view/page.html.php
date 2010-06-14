<div class="no-grid">
	<?php if ( !empty($view->error) ): ?>
	<h1><?php echo $view->pageTitle ?></h1>

	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( isset($view->body) ): ?>
	<ul class="crumbs">
		<li><a href="<?php echo $view->rootPath ?>"><?php echo $model->t('Home') ?></a> &rsaquo;</li>
		<?php if ( $view->parents ): ?>
		<?php foreach ( $view->parents as $permalink => $title ): ?>
		<li><a href="<?php echo $model->route('p/' . $permalink) ?>"><?php echo $title ?></a> &rsaquo;</li>
		<?php endforeach ?>
		<?php endif ?>
		<li><?php echo $view->pageTitle ?></li>
	</ul>

	<?php if ( $model->perm->check('admin page edit') || $model->perm->check('admin page delete') ): ?>
	<ul class="admin-toolbox">
		<?php if ( $model->perm->check('admin page edit') ): ?>
		<li><a class="button" href="<?php echo $view->rootPath ?>admin/pages/?action=edit&id=<?php   echo $view->nodeId ?>"><?php echo $model->t('Edit') ?></a></li>
		<?php endif ?>
		<?php if ( $model->perm->check('admin page delete') ): ?>
		<li><a class="button" href="<?php echo $view->rootPath ?>admin/pages/?action=delete&id=<?php echo $view->nodeId ?>"><?php echo $model->t('Delete') ?></a></li>
		<?php endif ?>
	</ul>
	<?php endif ?>

	<div style="clear: both;"></div>

	<h1><?php echo $view->pageTitle ?></h1>

	<?php echo $view->body ?>
	<?php endif ?>
</div>
