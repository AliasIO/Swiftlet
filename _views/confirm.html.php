<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<form class="hide" id="formConfirm" method="post" action="<?php echo $view->route($view->request) ?>">
		<fieldset>
			<p class="message notice">
				<?php echo $view->t($view->notice) ?>

				<br/><br/>

				<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

				<input type="submit" name="confirm" id="confirm" value="<?php echo $view->t('Yes, proceed') ?>"/>

				<a href="javascript: void(0);" onclick="history.go(-1);"><?php echo $view->t('No, go back') ?></a>
			</p>
		</fieldset>
	</form>
</div>
