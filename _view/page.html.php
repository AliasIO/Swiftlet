<?php if ( !empty($view->error) ): ?>
<h1><?php echo t('Page not found') ?></h1>

<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( isset($view->title) ): ?>
<ul class="crumbs">
	<?php if ( $view->parents ): ?>
	<?php foreach ( $view->parents as $permalink => $title ): ?>
	<li><a href="<?php echo $model->rewrite_url($contr->rootPath . 'page/?permalink=' . $permalink) ?>"><?php echo $title ?></a> &rsaquo;</li>
	<?php endforeach ?>
	<?php endif ?>
	<li><?php echo $view->title ?></li>
</ul>

<?php if ( $model->session->get('user auth') >= user::admin  ): ?>
<ul class="admin-toolbox">
	<li><a href="<?php echo $view->rootPath ?>admin/pages/?action=edit&id=<?php   echo $view->nodeId ?>"><?php echo t('Edit') ?></a></li>
	<li><a href="<?php echo $view->rootPath ?>admin/pages/?action=delete&id=<?php echo $view->nodeId ?>"><?php echo t('Delete') ?></a></li>
</ul>
<?php endif ?>

<div style="clear: both;"></div>

<h1><?php echo $view->title ?></h1>

<?php echo $view->body ?>
<?php endif ?>