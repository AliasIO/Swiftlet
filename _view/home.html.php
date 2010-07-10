<div class="no-grid">
	<h1><?php echo $model->t($view->pageTitle) ?></h1>

	<h2><?php echo $model->t('What\'s next?') ?></h2>

	<ul>
		<li><?php echo $model->t('Read the %sdocumentation%s.', array('<a href="' . $view->rootPath . 'docs/">', '</a>')) ?></li>
		<li><?php echo $model->t('Create and review the configuration file (copy %s to %s).', array('<code>/_config.default.php</code>', '<code>/_config.php</code>')) ?></li>
		<li><?php echo $model->t('Use the %splugin installer%s to install plugins (database connection required).', array('<a href="' . $view->rootPath . 'installer/">', '</a>')) ?></li>
		<li><?php echo $model->t('To change this page, replace or modify %1$s and %2$s.', array('<code>/home.php</code>', '<code>/_view/home.html.php</code>')) ?></li>
	</ul>

	<?php if ( $view->notices ): ?>
	<h2><?php echo $model->t('Attention') ?>:</h2>

	<?php foreach ( $view->notices as $notice ): ?>
	<p class="message notice"><?php echo $notice ?></p>
	<?php endforeach ?>
	<?php endif ?>
</div>
