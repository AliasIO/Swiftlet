<div class="no-grid">
	<h1><?php echo $this->pageTitle ?></h1>

	<p>
		<?php if ( $this->failures ): ?>
		<?php echo $this->t('%d Out of %d tests failed.', array($this->failures, $this->passes + $this->failures)) ?>
		<?php else: ?>
		<?php echo $this->t('All %d tests passed.', $this->passes + $this->failures) ?>
		<?php endif ?>
	</p>

	<?php foreach ( $this->tests as $test ): ?>
	<?php if ( $test['pass'] ): ?>
	<p class="message notice"><strong><?php echo $this->t('PASSED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php else: ?>
	<p class="message error"><strong><?php echo $this->t('FAILED') ?></strong>: <?php echo $test['test'] ?></p>
	<?php endif ?>
	<?php endforeach ?>
</div>
