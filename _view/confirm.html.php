<h1><?php echo t($contr->pageTitle) ?></h1>

<form class="hide" id="formConfirm" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
	<fieldset>
		<p class="message notice">
			<?php echo t($view->notice) ?>

			<br/><br/>

			<input type="hidden" name="get_data" value="<?php echo $view->getData ?>"/>

			<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

			<input type="submit" name="confirm" id="confirm" value="<?php echo t('Yes, proceed') ?>"/>

			<a href="javascript: void(0);" onclick="history.go(-1);"><?php echo t('No, go back') ?></a>
		</p>
	</fieldset>
</form>
