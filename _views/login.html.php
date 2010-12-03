<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<form id="form-login" method="post" action="<?php echo $this->route($this->request . ( !empty($app->input->GET_raw['ref']) ? '?ref=' . rawurlencode($app->input->GET_raw['ref']) : '' )) ?>">
		<fieldset>
			<dl>
				<dt><label for="username"><?php echo $this->t('Username') ?></label></dt>
				<dd>
					<input type="text" name="username" id="username" value="<?php echo $app->input->POST_html_safe['username'] ?>"/>
				</dd>
			</dl>
			<dl>
				<dt><label for="password"><?php echo $this->t('Password') ?></label></dt>
				<dd>
					<input type="password" name="password" id="password" value=""/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><label for="remember"><?php echo $this->t('Stay logged in') ?></label></dt>
				<dd>
					<input type="checkbox" name="remember" id="remember" value="1"<?php echo $app->input->POST_html_safe['remember'] ? ' checked="checked"' : '' ?>"/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Log in') ?>"/>
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
