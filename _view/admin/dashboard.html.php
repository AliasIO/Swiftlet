<div class="no-grid">
	<h1><?php echo $app->t($contr->pageTitle) ?></h1>

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
				<strong><a href="<?php echo $view->rootPath . $page['path'] ?>"><?php echo $app->t($page['name']) ?></a></strong>
				
				&mdash; <?php echo $app->t($page['description']) ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php endif ?>

		<?php endforeach ?>
		<?php endif ?>
	</div>

	<div class="span-5">
		<h2><?php echo $app->t('Installation details') ?></h2>
		
		<dl>
			<dt><?php echo $app->t('Swiftlet version') ?></dt>
			<dd><?php echo model::version ?></dd>
			
			<dt><?php echo $app->t('Plugins') ?></dt>
			<dd><?php echo count($app->pluginsHooked) ?><?php echo $view->newPlugins ? ', ' . $app->t('%1$s not installed', $view->newPlugins) : '' ?> (<a href="<?php echo $view->rootPath ?>installer/"><?php echo $app->t('installer') ?></a>)</dd>
		</dl>
		
		<h2><?php echo $app->t('Configuration') ?></h2>

		<dl>			
			<dt><?php echo $app->t('Environment') ?></dt>
			<dd><?php echo $app->testing ? $app->t('Testing (%1$sunit tests%2$s)', array('<a href="' . $view->rootPath . 'unit_tests/">', '</a>')) : $app->t('Production') ?></dd>
			
			<dt><?php echo $app->t('Debug mode') ?></dt>
			<dd><?php echo $app->debugMode ? $app->t('On') : $app->t('Off') ?></dd>
			
			<dt><?php echo $app->t('URL rewriting') ?></dt>
			<dd><?php echo $app->urlRewrite ? $app->t('On') : $app->t('Off') ?></dd>
			
			<dt><?php echo $app->t('Caching') ?></dt>
			<dd><?php echo $app->caching ? $app->t('On (%1$sclear%2$s)', array('<a href="?action=clear_cache">', '</a>')) : $app->t('Off') ?></dd>
		</dl>
	</div>
</div>
