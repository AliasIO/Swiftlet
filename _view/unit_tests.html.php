<div class="no-grid">
	<h1><?php echo $app->t($contr->pageTitle) ?></h1>

	<p>
		<?php if ( $view->failures ): ?>
		<?php echo $view->failures ?> Out of <?php echo $view->passes + $view->failures ?> tests failed.
		<?php else: ?>
		All <?php echo $view->passes + $view->failures ?> tests passed.
		<?php endif ?>
	</p>

	<?php foreach ( $view->tests as $test ): ?>
	<?php if ( $test['pass'] ): ?>
	<p class="message notice"><strong><?php echo $app->t('PASSED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php else: ?>
	<p class="message error"><strong><?php echo $app->t('FAILED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php endif ?>
	<?php endforeach ?>
</div>
