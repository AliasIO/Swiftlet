<h1><?php echo t($contr->pageTitle) ?></h1>

<?php if ( $view->pages ): ?>
<?php foreach ( $view->pages as $group => $pages ): ?>
<h2><?php echo $group ?></h2>

<?php if ( $pages ): ?>
<ul>
	<?php foreach ( $pages as $page ): ?>
	<li>
		<h4>
			<a href="<?php echo $view->rootPath . $page['path'] ?>"><?php echo t($page['name']) ?></a>
		</h4>
		<em><?php echo t($page['description']) ?></em>
	</li>
	<?php endforeach ?>
</ul>
<?php endif ?>

<?php endforeach ?>
<?php endif ?>