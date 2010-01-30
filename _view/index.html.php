<h1>
	<?php echo $view->siteName ?>
	<img style="vertical-align: middle;" src="<?php echo $view->viewPath ?>images/logo.png" width="120" height="120" alt="Logo"/>
	&hellip;<?php echo t('is up and running!') ?>
</h1>

<h2><?php echo t('Getting started') ?>:</h2>

<ul>
	<li><?php echo t('View the %s file.', '<a href="' . $view->rootPath . 'README.md"><code>/README.md</code></a>') ?></li>
	<li><?php echo t('View the documentation on %s.', '<a href="http://swiftlet.org/">http://swiftlet.org/</a>') ?></li>
	<li><?php echo t('To change this page, replace or modify %1$s and %2$s.', array('<code>/index.php</code>', '<code>/_view/index.html.php</code>')) ?></li>
	<li><?php echo t('To change global settings, modify %s.', '<code>/_config.php</code>') ?></li>
</ul>

<?php if ( $view->notices ): ?>
<h2><?php echo t('Attention') ?>:</h2>

<?php foreach ( $view->notices as $notice ): ?>
<p class="message notice"><?php echo $notice ?></p>
<?php endforeach ?>
<?php endif ?>
