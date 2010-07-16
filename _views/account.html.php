<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<?php if ( $app->session->get('user is owner') ): ?>
	<?php if ( $view->action == 'edit' ): ?>
	<h2><?php echo $view->t('Edit account') ?></h2>

	<p>
		<a class="button" href="./?action=create"><?php echo $view->t('Create a new account') ?></a> 
		<?php if ( $app->session->get('user id') != $view->userId ): ?>
		<a class="button caution" href="./?action=delete&id=<?php echo $view->userId ?>"><?php echo $view->t('Delete this account') ?></a>
		<?php endif ?>
	</p>

	<?php else: ?>
	<h2><?php echo $view->t('New account') ?></h2>
	<?php endif ?>
	<?php endif ?>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<form id="formAccount" method="post" action="./?action=<?php echo $view->action . ( $view->id ? '&id=' . $view->id : '' ) ?>" autocomplete="off">
		<fieldset>
			<?php if ( $view->action == 'edit' ): ?>
			<dl>
				<dt><?php echo $view->t('Id') ?></dt>
				<dd>
					<?php echo $view->userId ?>
				</dd>
			</dl>
			<?php endif ?>
			<dl>
				<dt><label for="username"><?php echo $view->t('Username') ?></label></dt>
				<dd>
					<?php if ( $app->session->get('user is owner') ): ?>
					<input type="text" name="username" id="username" value="<?php echo $app->input->POST_html_safe['username'] ?>"/>
					<?php else: ?>
					<?php echo $view->userUsername ?>
					<?php endif ?>
					
					<?php if ( isset($app->input->errors['username']) ): ?>
					<span class="error"><?php echo $app->input->errors['username'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="new_password"><?php echo $view->action == 'edit' ? $view->t('New password') : $view->t('Password') ?> (2x)</label></dt>
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
				<dt><label for="email"><?php echo $view->t('E-mail address') ?></label></dt>
				<dd>
					<input type="text" name="email" id="email" value="<?php echo $app->input->POST_html_safe['email'] ?>"/>
					
					<?php if ( isset($app->input->errors['email']) ): ?>
					<span class="error"><?php echo $view->t('Invalid e-mail address') ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<?php if ( $app->session->get('user is owner') ): ?>
			<dl>
				<dt><label for="auth"><?php echo $view->t('Owner privileges') ?></label></dt>
				<dd>
					<?php if ( $app->session->get('user id') != $view->userId ): ?>
					<input type="checkbox" name="owner" value="1" <?php echo $app->input->POST_html_safe['owner'] ? 'checked="checked"' : '' ?>/>
					<?php else: ?>
					<?php echo $view->t('Yes') ?>
					<?php endif ?>
				</dd>
			</dl>
			<?php endif ?>
		</fieldset>
		<?php if ( $view->prefs ): ?>
		<fieldset>
			<?php foreach ( $view->prefs as $pref ): ?>
			<dl>
				<dt><label for="pref-<?php echo $pref['id'] ?>"><?php echo $view->t($pref['pref']) ?></label></dt>
				<dd>
					<?php
					switch ( $pref['type'] )
					{
						case 'select':
							?>
							<select name="pref-<?php echo $pref['id'] ?>" id="pref-<?php echo $pref['id'] ?>">
							<option value="" ><?php echo $view->t('Select&hellip;') ?></option>
							<?php foreach ( $pref['options'] as $k => $v ): ?>
							<option value="<?php echo $view->h($k) ?>" <?php echo $view->h($k) == $app->input->POST_html_safe['pref-' . $pref['id']] ? 'selected="selected"' : '' ?>><?php echo $view->h($view->t($v)) ?></option>
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
					<span class="error"><?php echo $view->t('Invalid') ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<?php endforeach ?>
		</fieldset>
		<?php endif ?>
		<?php if ( $view->action == 'edit' && ( !$app->session->get('user is owner') || $app->session->get('user id') == $view->userId ) ): ?> 
		<fieldset>
			<dl>
				<dt><label for="password"><?php echo $view->t('Password') ?></label></dt>
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

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $view->t('Save settings') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>

	<?php if ( $app->session->get('user is owner') ): ?>
	<a name="users"></a>

	<h2><?php echo $view->t('All accounts') ?></h2>

	<?php if ( $view->users ): ?>
	<p>
		<?php echo $view->usersPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $view->t('Username') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $view->users as $id => $username ): ?>
			<tr>
				<td>
					<a href="?id=<?php echo $id ?>"><?php echo $username ?></a>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p>
		<?php echo $view->usersPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $view->t('No accounts') ?></em>
	</p>
	<?php endif ?>

	<?php if ( $view->action == 'create' ): ?>
	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the username field
		$('#username').focus();
		/* ]]> */ -->
	</script>
	<?php endif ?>
	<?php endif ?>
</div>
