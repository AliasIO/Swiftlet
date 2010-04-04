<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<div class="wrap-column">
	<div class="column left">
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
	</div>
</div>

<div class="wrap-column">
	<div class="column right">
		<h2>Installation details</h2>
		
		<dl>
			<dt>Swiftlet version</dt>
			<dd><?php echo model::version ?></dd>
		</dl>
		<dl>
			<dt>Plugins</dt>
			<dd><?php echo count($model->pluginsHooked) ?><?php echo $view->newPlugins ? ', ' . $model->t('%1$s not installed', $view->newPlugins) : '' ?> (<a href="<?php echo $view->rootPath ?>installer/"><?php echo $model->t('installer') ?></a>)</dd>
		</dl>
		<dl>
			<dt>Environment</dt>
			<dd><?php echo $model->testing ? $model->t('Testing (%1$srun unit tests%2$s)', array('<a href="' . $view->rootPath . 'unit_tests/">', '</a>')) : $model->t('Production') ?></dd>
		</dl>
		<dl>
			<dt>Debug mode</dt>
			<dd><?php echo $model->debugMode ? $model->t('On') : $model->t('Off') ?></dd>
		</dl>
		<dl>
			<dt>URL rewriting</dt>
			<dd><?php echo $model->urlRewrite ? $model->t('On') : $model->t('Off') ?></dd>
		</dl>
	</div>
</div>

<div style="clear: both;"></div>
