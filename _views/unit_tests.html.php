<div class="no-grid">
	<h1><?php echo $view->pageTitle ?></h1>

	<p>
		<?php if ( $view->failures ): ?>
		<?php echo $view->t('%d Out of %d tests failed.', array($view->failures, $view->passes + $view->failures) ?>
		<?php else: ?>
		<?php echo $view->t('All %d tests passed.', $view->passes + $view->failures) ?>
		<?php endif ?>
	</p>

	<?php foreach ( $view->tests as $test ): ?>
	<?php if ( $test['pass'] ): ?>
	<p class="message notice"><strong><?php echo $view->t('PASSED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php else: ?>
	<p class="message error"><strong><?php echo $view->t('FAILED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php endif ?>
	<?php endforeach ?>
</div>
