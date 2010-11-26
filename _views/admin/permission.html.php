<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<?php if ( $this->method == 'create' && $app->permission->check('admin permission create') || $this->method == 'edit' && $app->permission->check('admin permission edit') ): ?>
	<?php if ( $this->method == 'create' ): ?>
	<h2><?php echo $this->t('New role') ?></h2>
	<?php else: ?>
	<h2><?php echo $this->t('Edit role') ?></h2>
	<?php endif ?>

	<form id="form-role" method="post" action="<?php echo $this->route($this->request) ?>">
		<fieldset>
			<dl>
				<dt>
					<label for="name"><?php echo $this->t('Name') ?></label>
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

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Save role') ?>"/>

					<a href="<?php echo $this->route($this->path) ?>"><?php echo $this->t('Cancel') ?></a>
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
		<a class="button" href="<?php echo $this->route($this->path . '/create') ?>"><?php echo $this->t('Create a new role') ?></a>
	</p>
	<?php endif ?>
	<?php endif ?>

	<h2><?php echo $this->t('Roles') ?></h2>

	<?php if ( $this->roles ): ?>
	<ul>
		<?php foreach ( $this->roles as $role ): ?>
		<li>
			<h3><?php echo $this->t($role['name']) ?></h3>

			<?php if ( $app->permission->check('admin permission edit') || $app->permission->check('admin permission delete') ): ?>
			<p>
				<?php if ( $app->permission->check('admin permission edit') ): ?>
				<a class="button" href="<?php echo $this->route($this->path . '/edit/' . $role['id']) ?>"><?php echo $this->t('Edit this role') ?></a>
				<?php endif ?>
				<?php if ( $app->permission->check('admin permission delete') ): ?>
				<a class="button caution" href="<?php echo $this->route($this->path . '/delete/' . $role['id']) ?>"><?php echo $this->t('Delete this role') ?></a>
				<?php endif ?>
			</p>
			<?php endif ?>

			<?php if ( $this->method == 'add_user' && $this->id == $role['id'] ): ?>
			<h4><?php echo $this->t('Add user') ?></h4>

			<form id="form-user<?php echo $role['id'] ?>" method="post" action="<?php echo $this->route($this->path . '/add_user/' . $role['id']) ?>">
				<fieldset>
					<dl>
						<dt>
							<label for="user"><?php echo $this->t('User') ?></label>
						</dt>
						<dd>
							<select name="user" id="user">
								<option value=""><?php echo $this->t('Select a user') ?></option>
								<?php if ( $this->users ): ?>
								<?php foreach ( $this->users as $user ): ?>
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

							<input type="submit" name="form-submit-2" id="form-submit-2" value="<?php echo $this->t('Add user') ?>"/>

							<a href="<?php echo $this->route($this->path) ?>"><?php echo $this->t('Cancel') ?></a>
						</dd>
					</dl>
				</fieldset>
			</form>
			<?php endif ?>

			<h4><?php echo $this->t('Users') ?></h4>

			<p>
				<a class="button" href="<?php echo $this->route($this->path . '/add_user/' . $role['id']) ?>"><?php echo $this->t('Add a user') ?></a>
			</p>

			<?php if ( $role['users'] ): ?>
			<ul>
				<?php foreach ( $role['users'] as $user ): ?>
				<li>
					<a class="button caution" href="<?php echo $this->route($this->path . '/remove_user/' . $role['id'] . '/' . $user['id']) ?>"><?php echo $this->t('Remove') ?></a>
					<?php echo $user['username'] ?>
				</li>
				<?php endforeach ?>
			</ul>
			<?php else: ?>
			<p>
				<em><?php echo $this->t('This role has no users') ?></em>
			</p>
			<?php endif ?>
		</li>
	</ul>

	<?php endforeach ?>

	<h2><?php echo $this->t('Permissions') ?></h2>

	<form id="form-perm" method="post" action="<?php echo $this->route($this->path) ?>">
		<fieldset>
			<table>
				<thead>
					<tr>
						<th><?php $this->t('Permission') ?></th>
						<?php foreach ( $this->roles as $role ): ?>
						<th><?php echo $this->t($role['name']) ?></th>
						<?php endforeach ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->permissionsGroups as $group => $permissions ): ?>
					<tr>
						<th>
							<strong><?php echo $this->t($group) ?></strong>
						</th>
						<?php foreach ( $this->roles as $role ): ?>
						<td><br/></td>
						<?php endforeach ?>
					</tr>
					<?php foreach ( $permissions as $permission ): ?>
					<tr>
						<th>
							<?php echo $this->t($permission['desc']) ?>
						</th>
						<?php foreach ( $this->roles as $role ): ?>
						<td>
							<select name="value[<?php echo $permission['id'] ?>][<?php echo $role['id'] ?>]" id="value_<?php echo $permission['id'] ?>_<?php echo $role['id'] ?>">
								<option value="<?php echo Permission_Plugin::YES   ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission_Plugin::YES   ? ' selected="selected"' : '' ?>><?php echo $this->t('Yes')   ?></option>
								<option value="<?php echo Permission_Plugin::NO    ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission_Plugin::NO    ? ' selected="selected"' : '' ?>><?php echo $this->t('No')    ?></option>
								<option value="<?php echo Permission_Plugin::NEVER ?>"<?php echo $app->input->POST_html_safe['value'][$permission['id']][$role['id']] == Permission_Plugin::NEVER ? ' selected="selected"' : '' ?>><?php echo $this->t('Never') ?></option>
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

					<input type="submit" name="form-submit-3" id="form-submit-3" value="<?php echo $this->t('Save permissions') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('No roles') ?><em>
	</p>
	<?php endif ?>
</div>
