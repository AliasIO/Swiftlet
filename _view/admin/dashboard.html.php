<div class="no-grid">
	<h1><?php echo $model->t($contr->pageTitle) ?></h1>

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
				<strong><a href="<?php echo $view->rootPath . $page['path'] ?>"><?php echo $model->t($page['name']) ?></a></strong>
				
				&mdash; <?php echo $model->t($page['description']) ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php endif ?>

		<?php endforeach ?>
		<?php endif ?>
	</div>

	<div class="span-5">
		<h2><?php echo $model->t('Installation details') ?></h2>
		
		<dl>
			<dt><?php echo $model->t('Swiftlet version') ?></dt>
			<dd><?php echo model::version ?></dd>
			
			<dt><?php echo $model->t('Plugins') ?></dt>
			<dd><?php echo count($model->pluginsHooked) ?><?php echo $view->newPlugins ? ', ' . $model->t('%1$s not installed', $view->newPlugins) : '' ?> (<a href="<?php echo $view->rootPath ?>installer/"><?php echo $model->t('installer') ?></a>)</dd>
		</dl>
		
		<h2><?php echo $model->t('Configuration') ?></h2>

		<dl>			
			<dt><?php echo $model->t('Environment') ?></dt>
			<dd><?php echo $model->testing ? $model->t('Testing (%1$sunit tests%2$s)', array('<a href="' . $view->rootPath . 'unit_tests/">', '</a>')) : $model->t('Production') ?></dd>
			
			<dt><?php echo $model->t('Debug mode') ?></dt>
			<dd><?php echo $model->debugMode ? $model->t('On') : $model->t('Off') ?></dd>
			
			<dt><?php echo $model->t('URL rewriting') ?></dt>
			<dd><?php echo $model->urlRewrite ? $model->t('On') : $model->t('Off') ?></dd>
			
			<dt><?php echo $model->t('Caching') ?></dt>
			<dd><?php echo $model->caching ? $model->t('On (%1$sclear%2$s)', array('<a href="?action=clear_cache">', '</a>')) : $model->t('Off') ?></dd>
		</dl>
	</div>
</div>
