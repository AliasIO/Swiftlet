<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<?php if ( $view->action == 'create' && $app->permission->check('admin permission create') || $view->action == 'edit' && $app->permission->check('admin permission edit') ): ?>
	<?php if ( $view->action == 'create' ): ?>
	<h2><?php echo $view->t('New role') ?></h2>
	<?php else: ?>
	<h2><?php echo $view->t('Edit role') ?></h2>
	<?php endif ?>

	<form id="formRole" method="post" action="./?<?php echo $view->action == 'edit' ? 'id=' . $view->id . '&' : '' ?>action=<?php echo $view->action ?>">
		<fieldset>
			<dl>
				<dt>
					<label for="name"><?php echo $view->t('Name') ?></label>
				</dt>
				<dd>
					<input type="text" name="name" id="name" value="<?php echo $app->input->POST_html_safe['name'] ?>"/>

					<?php if ( isset($app->input->errors['name']) ): ?>
					<span class="error"><?php echo $app->input->errors['name'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $view->t('Save role') ?>"/>

					<a href="./"><?php echo $view->t('Cancel') ?></a>
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
	<?php if ( $app->permission->check('admin permission role create') ): ?>
	<p>
		<a class="button" href="./?action=create"><?php echo $view->t('Create a new role') ?></a>
	</p>
	<?php endif ?>
	<?php endif ?>

	<h2><?php echo $view->t('Roles') ?></h2>

	<?php if ( $view->roles ): ?>
	<ul>
		<?php foreach ( $view->roles as $role ): ?>
		<li>
			<h3><?php echo $view->t($role['name']) ?></h3>

			<?php if ( $app->permission->check('admin permission edit') || $app->permission->check('admin permission delete') ): ?>
			<p>
				<?php if ( $app->permission->check('admin permission edit') ): ?>
				<a class="button" href="./?id=<?php echo $role['id'] ?>&action=edit"  ><?php echo $view->t('Edit this role') ?></a>
				<?php endif ?>
				<?php if ( $app->permission->check('admin permission delete') ): ?>
				<a class="button caution" href="./?id=<?php echo $role['id'] ?>&action=delete"><?php echo $view->t('Delete this role') ?></a>
				<?php endif ?>
			</p>
			<?php endif ?>

			<?php if ( $view->action == 'add' && $view->id == $role['id'] ): ?>
			<h4><?php echo $view->t('Add user') ?></h4>

			<form id="formUser<?php echo $role['id'] ?>" method="post" action="./?action=add&id=<?php echo $role['id'] ?>">
				<fieldset>
					<dl>
						<dt>
							<label for="user"><?php echo $view->t('User') ?></label>
						</dt>
						<dd>
							<select name="user" id="user">
								<option value=""><?php echo $view->t('Select a user') ?></option>
								<?php if ( $view->users ): ?>
								<?php foreach ( $view->users as $user ): ?>
								<option value="<?php echo $user['id'] ?>"><?php echo $user['username'] ?></option>
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
							<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

							<input type="submit" name="form-submit-2" id="form-submit-2" value="<?php echo $view->t('Add user') ?>"/>

							<a href="./"><?php echo $view->t('Cancel') ?></a>
						</dd>
					</dl>
				</fieldset>
			</form>
			<?php endif ?>
			
			<h4><?php echo $view->t('Users') ?></h4>

			<p>
				<a class="button" href="./?id=<?php echo $role['id'] ?>&action=add"><?php echo $view->t('Add a user') ?></a>
			</p>

			<?php if ( $role['users'] ): ?>
			<ul>
				<?php foreach ( $role['users'] as $user ): ?>
				<li>
					<a class="button caution" href="./?id=<?php echo $role['id'] ?>&user_id=<?php echo $user['id'] ?>&action=remove"><?php echo $view->t('Remove') ?></a>
					<?php echo $user['username'] ?>
				</li>
				<?php endforeach ?>
			</ul>
			<?php else: ?>
			<p>
				<em><?php echo $view->t('This role has no users') ?></em>
			</p>
			<?php endif ?>
		</li>
	</ul>

	<?php endforeach ?>

	<h2><?php echo $view->t('Permissions') ?></h2>

	<form id="formPerm" method="post" action="./">
		<fieldset>
			<table>
				<thead>
					<tr>
						<th><?php $view->t('Permission') ?></th>
						<?php foreach ( $view->roles as $role ): ?>
						<th><?php echo $view->t($role['name']) ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $view->permissionsGroups as $group => $permissions ): ?>
					<tr>
						<th>
							<strong><?php echo $view->t($group) ?></strong>
						</th>
						<?php foreach ( $view->roles as $role ): ?>
						<td><br/></td>
						<?php endforeach ?>
					</tr>
					<?php foreach ( $permissions as $permission ): ?>
					<tr>
						<th>
							<?php echo $view->t($permission['desc']) ?>
						</th>
						<?php foreach ( $view->roles as $role ): ?>
						<td>
							<select name="value[<?php echo $permission['id'] ?>][<?php echo $role['id'] ?>]" id="value_<?php echo $permission['id'] ?>_<?php echo $role['id'] ?>">
								<option value="<?php echo Permission::YES   ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission::YES   ? ' selected="selected"' : '' ?>><?php echo $view->t('Yes')   ?></option>
								<option value="<?php echo Permission::NO    ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission::NO    ? ' selected="selected"' : '' ?>><?php echo $view->t('No')    ?></option>
								<option value="<?php echo Permission::NEVER ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission::NEVER ? ' selected="selected"' : '' ?>><?php echo $view->t('Never') ?></option>
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
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit-3" id="form-submit-3" value="<?php echo $view->t('Save permissions') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $view->t('No roles') ?><em>
	</p>
	<?php endif ?>
</div>
