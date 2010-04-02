<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( $view->pages ): ?>
<?php foreach ( $view->pages as $group => $pages ): ?>
<h2><?php echo $group ?></h2>

<?php if ( $pages ): ?>
<ul>
	<?php foreach ( $pages as $page ): ?>
	<li>
		<strong><a href="<?php echo $view->rootPath . $page['path'] ?>"><?php echo $model->t($page['name']) ?></a></strong>
		
		<em><?php echo $model->t($page['description']) ?></em>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>

<?php endforeach ?>
<?php endif ?>
