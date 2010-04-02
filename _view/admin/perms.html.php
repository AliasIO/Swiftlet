<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'create' && $model->perm->check('admin perm create') || $view->action == 'edit' && $model->perm->check('admin perm edit') ): ?>
<?php if ( $view->action == 'create' ): ?>
<h2><?php echo $model->t('New role') ?></h2>
<?php else: ?>
<h2><?php echo $model->t('Edit role') ?></h2>
<?php endif ?>

<form id="formRole" method="post" action="./?<?php echo $view->action == 'edit' ? 'id=' . $view->id . '&' : '' ?>action=<?php echo $view->action ?>">
	<fieldset>
		<dl>
			<dt>
				<label for="name"><?php echo $model->t('Name') ?></label>
			</dt>
			<dd>
				<input type="text" name="name" id="name" value="<?php echo $model->POST_html_safe['name'] ?>"/>

				<?php if ( isset($model->form->errors['name']) ): ?>
				<span class="error"><?php echo $model->form->errors['name'] ?></span>
				<?php endif ?>
			</dd>
		</dl>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Save role') ?>"/>

				<a href="./"><?php echo $model->t('Cancel') ?></a>
			</dd>
		</dl>
	</fieldset>
</form>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	// Focus the name field
	$('#name').focus();
	/* ]]> */ -->
</script><?php else: ?>
<?php if ( $model->perm->check('admin perm role create') ): ?>
<p>
	<a class="button" href="./?action=create"><?php echo $model->t('Create a new role') ?></a>
</p>
<?php endif ?>
<?php endif ?>

<h2><?php echo $model->t('Roles') ?></h2>

<?php if ( $view->roles ): ?>
<ul>
	<?php foreach ( $view->roles as $role ): ?>
	<li>
		<h4><?php echo $model->h($model->t($role['name'])) ?></h4>

		<?php if ( $model->perm->check('admin perm edit') || $model->perm->check('admin perm delete') ): ?>
		<p>
			<?php if ( $model->perm->check('admin perm edit') ): ?>
			<a class="button" href="./?id=<?php echo $role['id'] ?>&action=edit"  ><?php echo $model->t('Edit this role') ?></a>
			<?php endif ?>
			<?php if ( $model->perm->check('admin perm delete') ): ?>
			<a class="button" href="./?id=<?php echo $role['id'] ?>&action=delete"><?php echo $model->t('Delete this role') ?></a>
			<?php endif ?>
		</p>
		<?php endif ?>

		<?php if ( $view->action == 'add' && $view->id == $role['id'] ): ?>
		<h5><?php echo $model->t('Add user') ?></h5>

		<form id="formUser<?php echo $role['id'] ?>" method="post" action="./?action=add&id=<?php echo $role['id'] ?>">
			<fieldset>
				<dl>
					<dt>
						<label for="user"><?php echo $model->t('User') ?></label>
					</dt>
					<dd>
						<select name="user" id="user">
							<option value=""><?php echo $model->t('Select a user') ?></option>
							<?php if ( $view->users ): ?>
							<?php foreach ( $view->users as $user ): ?>
							<option value="<?php echo $user['id'] ?>"><?php echo $model->h($user['username']) ?></option>
							<?php endforeach ?>
							<?php endif ?>
						</select>
					</dd>
				</dl>
			</fieldset>
			<fieldset>
				<dl>
					<dt><br/></dt>
					<dd>
						<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

						<input type="submit" name="form-submit-2" id="form-submit-2" value="<?php echo $model->t('Add user') ?>"/>

						<a href="./"><?php echo $model->t('Cancel') ?></a>
					</dd>
				</dl>
			</fieldset>
		</form>
		<?php endif ?>
		
		<h5><?php echo $model->t('Users') ?></h5>

		<p>
			<a class="button" href="./?id=<?php echo $role['id'] ?>&action=add"><?php echo $model->t('Add a user') ?></a>
		</p>

		<?php if ( $role['users'] ): ?>
		<ul>
			<?php foreach ( $role['users'] as $user ): ?>
			<li>
				<a class="button" href=".?id=<?php echo $role['id'] ?>&user_id=<?php echo $user['id'] ?>&action=remove"><?php echo $model->t('Remove') ?></a>
				<?php echo $user['username'] ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php else: ?>
		<p>
			<em><?php echo $model->t('This role has no users') ?></em>
		</p>
		<?php endif ?>
	</li>
</ul>

<?php endforeach ?>

<h2><?php echo $model->t('Permissions') ?></h2>

<form id="formPerm" method="post" action="./">
	<fieldset>
		<table>
			<thead>
				<tr>
					<th><?php $model->t('Permission') ?></th>
					<?php foreach ( $view->roles as $role ): ?>
					<th><?php echo $model->h($model->t($role['name'])) ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $view->permsGroups as $group => $perms ): ?>
				<tr>
					<th>
						<strong><?php echo $model->h($model->t($group)) ?></strong>
					</th>
					<?php foreach ( $view->roles as $role ): ?>
					<td><br/></td>
					<?php endforeach ?>
				</tr>
				<?php foreach ( $perms as $perm ): ?>
				<tr>
					<th>
						<?php echo $model->h($model->t($perm['desc'])) ?>
					</th>
					<?php foreach ( $view->roles as $role ): ?>
					<td>
						<select name="value[<?php echo $perm['id'] ?>][<?php echo $role['id'] ?>]" id="value_<?php echo $perm['id'] ?>_<?php echo $role['id'] ?>">
							<option value="<?php echo perm::yes   ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::yes   ? ' selected="selected"' : '' ?>><?php echo $model->t('Yes')   ?></option>
							<option value="<?php echo perm::no    ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::no    ? ' selected="selected"' : '' ?>><?php echo $model->t('No')    ?></option>
							<option value="<?php echo perm::never ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::never ? ' selected="selected"' : '' ?>><?php echo $model->t('Never') ?></option>
						</select>
					</td>
					<?php endforeach ?>
				</tr>
				<?php endforeach ?>
				<?php endforeach ?>
			</tbody>
		</table>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" name="form-submit-3" id="form-submit-3" value="<?php echo $model->t('Save permissions') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>
<?php else: ?>
<p>
	<em><?php echo $model->t('No roles') ?><em>
</p>
<?php endif ?>
