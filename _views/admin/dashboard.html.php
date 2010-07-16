<div class="no-grid">
	<h1><?php echo $view->pageTitle ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>
</div>

<div class="grid">
	<div class="span-7">
		<?php if ( $view->pages ): ?>
		<?php foreach ( $view->pages as $group => $pages ): ?>
		<h2><?php echo $group ?></h2>

		<?php if ( $pages ): ?>
		<ul>
			<?php foreach ( $pages as $page ): ?>
			<li>
				<strong><a href="<?php echo $page['path'] ?>"><?php echo $view->t($page['name']) ?></a></strong>

				&mdash; <?php echo $view->t($page['description']) ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php endif ?>

		<?php endforeach ?>
		<?php endif ?>
	</div>

	<div class="span-5">
		<h2><?php echo $view->t('Installation details') ?></h2>

		<dl>
			<dt><?php echo $view->t('Swiftlet version') ?></dt>
			<dd><?php echo Application::VERSION ?></dd>

			<dt><?php echo $view->t('Plugins') ?></dt>
			<dd><?php echo count($app->pluginsHooked) ?><?php echo $view->newPlugins ? ', ' . $view->t('%1$s not installed', $view->newPlugins) : '' ?> (<a href="<?php echo $view->route('installer') ?>"><?php echo $view->t('installer') ?></a>)</dd>
		</dl>

		<h2><?php echo $view->t('Configuration') ?></h2>

		<dl>
			<dt><?php echo $view->t('Environment') ?></dt>
			<dd><?php echo $app->testing ? $view->t('Testing (%1$sunit tests%2$s)', array('<a href="' . $view->route('unit_tests') . '">', '</a>')) : $view->t('Production') ?></dd>

			<dt><?php echo $view->t('Debug mode') ?></dt>
			<dd><?php echo $app->debugMode ? $view->t('On') : $view->t('Off') ?></dd>

			<dt><?php echo $view->t('URL rewriting') ?></dt>
			<dd><?php echo $app->urlRewrite ? $view->t('On') : $view->t('Off') ?></dd>

			<dt><?php echo $view->t('Caching') ?></dt>
			<dd><?php echo $app->caching ? $view->t('On (%1$sclear%2$s)', array('<a href="?action=clear_cache">', '</a>')) : $view->t('Off') ?></dd>
		</dl>
	</div>
</div>
