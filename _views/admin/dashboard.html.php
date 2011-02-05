<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->pageTitle ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>
</div>

<div class="grid">
	<?php if ( $this->app->permission->check('admin dashboard overview access') ): ?>
	<div class="span-7">
	<?php else: ?>
	<div class="span-12">
	<?php endif ?>
		<?php if ( $this->pages ): ?>
		<?php foreach ( $this->pages as $group => $pages ): ?>
		<h2><?php echo $group ?></h2>

		<?php if ( $pages ): ?>
		<ul>
			<?php foreach ( $pages as $page ): ?>
			<li>
				<strong><a href="<?php echo $page['path'] ?>"><?php echo $this->t($page['name']) ?></a></strong>

				&mdash; <?php echo $this->t($page['description']) ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php endif ?>

		<?php endforeach ?>
		<?php endif ?>
	</div>

	<?php if ( $this->app->permission->check('admin dashboard overview access') ): ?>
	<div class="span-5">
		<h2><?php echo $this->t('Installation details') ?></h2>

		<dl>
			<dt><?php echo $this->t('Swiftlet version') ?></dt>
			<dd><?php echo Application::VERSION ?></dd>

			<dt><?php echo $this->t('Plugins') ?></dt>
			<dd><?php echo count($app->pluginsHooked) ?><?php echo $this->newPlugins ? ', ' . $this->t('%1$s not installed', $this->newPlugins) : '' ?> (<a href="<?php echo $this->route('installer') ?>"><?php echo $this->t('installer') ?></a>)</dd>
		</dl>

		<h2><?php echo $this->t('Configuration') ?></h2>

		<dl>
			<dt><?php echo $this->t('Environment') ?></dt>
			<dd><?php echo $app->config['testing'] ? $this->t('Testing (%1$sunit tests%2$s)', array('<a href="' . $this->route('test') . '">', '</a>')) : $this->t('Production') ?></dd>

			<dt><?php echo $this->t('Debug mode') ?></dt>
			<dd><?php echo $app->config['debugMode'] ? $this->t('On') : $this->t('Off') ?></dd>

			<dt><?php echo $this->t('URL rewriting') ?></dt>
			<dd><?php echo $app->config['urlRewrite'] ? $this->t('On') : $this->t('Off') ?></dd>

			<dt><?php echo $this->t('Caching') ?></dt>
			<dd><?php echo $app->config['caching'] ? $this->t('On (%1$sclear%2$s)', array('<a href="?action=clear_cache">', '</a>')) : $this->t('Off') ?></dd>
		</dl>
	</div>
	<?php endif ?>
</div>
