<div id="documentation">
	<h1><?php echo $model->t($contr->pageTitle) ?></h1>

	<div class="wrap-column-left">
		<div class="column">
			<?php if ( isset($view->pageContents) ): ?>
			<ul class="crumbs" style="float: none; text-align: center;">
				<li style="float: left;"><?php echo $view->pagePrev ?></li>
				<li>&nbsp;<?php echo $view->pageUp ?>&nbsp;</li>
				<li style="float: right;"><?php echo $view->pageNext ?></li>
			</ul>

			<div style="clear: both;"></div>

			<?php echo $view->pageContents ?>

			<ul class="crumbs" style="float: none; text-align: center;">
				<li style="float: left;"><?php echo $view->pagePrev ?></li>
				<li>&nbsp;<?php echo $view->pageUp ?>&nbsp;</li>
				<li style="float: right;"><?php echo $view->pageNext ?></li>
			</ul>

			<div style="clear: both;"></div>
			<?php else: ?>
				<div id="content">
					<h1>The page you requested does not exist.</h1>
				</div>
			<?php endif ?>
		</div>
	</div>
	
	<div class="wrap-column-right">
		<div class="column">
			<?php echo $view->overview ?>
		</div>
	</div>

	<div style="clear: both;"></div>
</div>
