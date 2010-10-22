<div class="no-grid">
	<?php if ( isset($this->body) ): ?>
	<?php if ( $app->permission->check('admin page edit') || $app->permission->check('admin page delete') ): ?>
	<ul class="admin-toolbox">
		<?php if ( $app->permission->check('admin page edit') ): ?>
		<li><a class="button" href="<?php echo $this->route('admin/page/edit/' . $this->nodeId) ?>"><?php echo $this->t('Edit') ?></a></li>
		<?php endif ?>
	</ul>
	<?php endif ?>

	<?php if ( $this->parents ): ?>
	<ul class="crumbs">
		<?php foreach ( $this->parents as $path => $title ): ?>
		<li><a href="<?php echo $this->route($path) ?>"><?php echo $title ?></a> &rsaquo;</li>
		<?php endforeach ?>
		<li><?php echo $this->pageTitle ?></li>
	</ul>
	<?php endif ?>
	<?php endif ?>

	<h1><?php echo $this->pageTitle ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<?php if ( isset($this->body) ): ?>
	<?php echo $this->body ?>
	<?php endif ?>
</div>
