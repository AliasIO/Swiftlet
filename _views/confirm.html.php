<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<form class="hide" id="form-confirm" method="post" action="<?php echo $this->route($this->request) ?>">
		<fieldset>
			<p class="message notice">
				<?php echo $this->t($this->notice) ?>

				<br/><br/>

				<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

				<input type="submit" name="confirm" id="confirm" value="<?php echo $this->t('Yes, proceed') ?>"/>

				<a href="javascript: void(0);" onclick="history.go(-1);"><?php echo $this->t('No, go back') ?></a>
			</p>
		</fieldset>
	</form>
</div>
