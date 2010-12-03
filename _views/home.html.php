<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->pageTitle ?></h1>

	<h2><?php echo $this->t('What\'s next?') ?></h2>

	<ul>
		<li><?php echo $this->t('Read the %sdocumentation%s.', array('<a href="' . $this->rootPath . 'doc">', '</a>')) ?></li>
		<li><?php echo $this->t('Create and review the configuration file (copy %s to %s).', array('<code>/_config.default.php</code>', '<code>/_config.php</code>')) ?></li>
		<li><?php echo $this->t('Use the %splugin installer%s to install plugins (database connection required).', array('<a href="' . $this->rootPath . 'installer">', '</a>')) ?></li>
		<li><?php echo $this->t('To change this page, replace or modify %1$s and %2$s.', array('<code>/_controllers/Home.php</code>', '<code>/_views/home.html.php</code>')) ?></li>
	</ul>

	<?php if ( $this->notices ): ?>
	<h2><?php echo $this->t('Attention') ?>:</h2>

	<?php foreach ( $this->notices as $notice ): ?>
	<p class="message notice"><?php echo $notice ?></p>
	<?php endforeach ?>
	<?php endif ?>
</div>
