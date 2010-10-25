<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( $app->session->get('user is owner') ): ?>
	<?php if ( !$this->method || $this->method == 'edit' ): ?>
	<h2><?php echo $this->t('Edit account') ?></h2>

	<p>
		<a class="button" href="<?php echo $this->route($this->path . '/create') ?>"><?php echo $this->t('Create a new account') ?></a>
		<?php if ( $app->session->get('user id') != $this->userId ): ?>
		<a class="button caution" href="<?php echo $this->route($this->path . '/delete/' . $this->userId) ?>"><?php echo $this->t('Delete this account') ?></a>
		<?php endif ?>
	</p>

	<?php else: ?>
	<h2><?php echo $this->t('New account') ?></h2>
	<?php endif ?>
	<?php endif ?>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<form id="form-account" method="post" action="<?php echo $this->route($this->request) ?>" autocomplete="off">
		<fieldset>
			<dl>
				<dt><label for="username"><?php echo $this->t('Username') ?></label></dt>
				<dd>
					<?php if ( $app->session->get('user is owner') ): ?>
					<input type="text" name="username" id="username" value="<?php echo $app->input->POST_html_safe['username'] ?>"/>
					<?php else: ?>
					<?php echo $this->userUsername ?>
					<?php endif ?>

					<?php if ( isset($app->input->errors['username']) ): ?>
					<span class="error"><?php echo $app->input->errors['username'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="new_password"><?php echo $this->method == 'edit' ? $this->t('New password') : $this->t('Password') ?> (2x)</label></dt>
				<dd>
					<input type="password" name="new_password" id="new_password"/>

					<?php if ( isset($app->input->errors['new_password']) ): ?>
					<span class="error"><?php echo $app->input->errors['new_password'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="password" name="new_password_confirm" id="new_password_confirm"/>

					<?php if ( isset($app->input->errors['new_password_repeat']) ): ?>
					<span class="error"><?php echo $app->input->errors['new_password_repeat'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="email"><?php echo $this->t('E-mail address') ?></label></dt>
				<dd>
					<input type="text" name="email" id="email" value="<?php echo $app->input->POST_html_safe['email'] ?>"/>

					<?php if ( isset($app->input->errors['email']) ): ?>
					<span class="error"><?php echo $this->t('Invalid e-mail address') ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<?php if ( $app->session->get('user is owner') ): ?>
			<dl>
				<dt><label for="auth"><?php echo $this->t('Owner privileges') ?></label></dt>
				<dd>
					<?php if ( $app->session->get('user id') != $this->userId ): ?>
					<input type="checkbox" name="owner" value="1" <?php echo $app->input->POST_html_safe['owner'] ? 'checked="checked"' : '' ?>/>
					<?php else: ?>
					<?php echo $this->t('Yes') ?>
					<?php endif ?>
				</dd>
			</dl>
			<?php endif ?>
		</fieldset>
		<?php if ( $this->prefs ): ?>
		<fieldset>
			<?php foreach ( $this->prefs as $pref ): ?>
			<dl>
				<dt><label for="pref-<?php echo $pref['id'] ?>"><?php echo $this->t($pref['pref']) ?></label></dt>
				<dd>
					<?php
					switch ( $pref['type'] )
					{
						case 'select':
							?>
							<select name="pref-<?php echo $pref['id'] ?>" id="pref-<?php echo $pref['id'] ?>">
							<option value="" ><?php echo $this->t('Select&hellip;') ?></option>
							<?php foreach ( $pref['options'] as $k => $v ): ?>
							<option value="<?php echo $this->h($k) ?>" <?php echo $this->h($k) == $app->input->POST_html_safe['pref-' . $pref['id']] ? 'selected="selected"' : '' ?>><?php echo $this->h($this->t($v)) ?></option>
							<?php endforeach ?>
							</select>
							<?php

							break;
						case 'text':
							?>
							<input type="text" name="pref-<?php echo $pref['id'] ?>" id="pref-<?php echo $pref['id'] ?>" value="<?php echo $app->input->POST_html_safe['pref-' . $pref['id']] ?>"/>
							<?php

							break;
						case 'checkbox':
							?>
							<input type="checkbox" name="pref-<?php echo $pref['id'] ?>" id="pref-<?php echo $pref['id'] ?>" value="1" <?php echo $app->input->POST_html_safe['pref-' . $pref['id']] ? 'checked="checked"' : '' ?>/>
							<?php

							break;
					}
					?>

					<?php if ( isset($app->input->errors['pref-' . $pref['id']]) ): ?>
					<span class="error"><?php echo $this->t('Invalid') ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<?php endforeach ?>
		</fieldset>
		<?php endif ?>
		<?php if ( $this->method != 'create' && ( !$app->session->get('user is owner') || $app->session->get('user id') == $this->id || !$this->id ) ): ?>
		<fieldset>
			<dl>
				<dt><label for="password"><?php echo $this->t('Password') ?></label></dt>
				<dd>
					<input type="password" name="password" id="password"/>

					<?php if ( isset($app->input->errors['password']) ): ?>
					<span class="error"><?php echo $app->input->errors['password'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
		</fieldset>
		<?php endif ?>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Save settings') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>

	<?php if ( $app->session->get('user is owner') ): ?>
	<a name="users"></a>

	<h2><?php echo $this->t('All accounts') ?></h2>

	<?php if ( $this->users ): ?>
	<p>
		<?php echo $this->usersPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $this->t('Username') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $this->users as $id => $username ): ?>
			<tr>
				<td>
					<a href="<?php echo $this->route($this->path . '/edit/' . $id) ?>"><?php echo $username ?></a>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p>
		<?php echo $this->usersPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('No accounts') ?></em>
	</p>
	<?php endif ?>

	<?php if ( $this->method == 'create' ): ?>
	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the username field
		$('#username').focus();
		/* ]]> */ -->
	</script>
	<?php endif ?>
	<?php endif ?>
</div>
