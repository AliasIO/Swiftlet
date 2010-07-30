<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<form id="formLogin" method="post" action="<?php echo !empty($app->input->GET_raw['ref']) ? '?ref=' . rawurlencode($app->input->GET_raw['ref']) : '' ?>">
		<fieldset>
			<dl>
				<dt><label for="username"><?php echo $view->t('Username') ?></label></dt>
				<dd>
					<input type="text" name="username" id="username" value="<?php echo $app->input->POST_html_safe['username'] ?>"/>
				</dd>
			</dl>
			<dl>
				<dt><label for="password"><?php echo $view->t('Password') ?></label></dt>
				<dd>
					<input type="password" name="password" id="password" value=""/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $view->t('Log in') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>

	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the username field
		$('#username').focus();
		/* ]]> */ -->
	</script>
</div>
