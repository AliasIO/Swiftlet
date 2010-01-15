<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'create' ): ?>
<h2><?php echo t('New role') ?></h2>

<form id="formRole" method="post" action="./?action=create">
	<fieldset>
		<dl>
			<dt>
				<label for="name"><?php echo t('Name') ?></label>
			</dt>
			<dd>
				<input type="text" class="text" name="name" id="name" value="<?php echo $model->POST_html_safe['name'] ?>"/>

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

				<input type="submit" class="button" name="form-submit" id="form-submit" value="<?php echo t('Create role') ?>"/>

				<a href="./"><?php echo t('Cancel') ?></a>
			</dd>
		</dl>
	</fieldset>
</form>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	// Focus the name field
	$(function() {
		$('#name').focus();
	});
	/* ]]> */ -->
</script><?php else: ?>
<p>
	<a href="./?action=create"><?php echo $model->t('Create a new role') ?></a>
</p>
<?php endif ?>

<h2><?php echo t('Roles') ?></h2>

<?php if ( $view->roles ): ?>
<ul>
	<?php foreach ( $view->roles as $role ): ?>
	<li>
		<h4><?php echo h(t($role['name'])) ?></h4>

		<p>
			<a href="./?id=<?php echo $role['id'] ?>&action=edit"  ><?php echo $model->t('Edit this role') ?></a> |
			<a href="./?id=<?php echo $role['id'] ?>&action=delete"><?php echo $model->t('Delete this role') ?></a>
		</p>

		<?php if ( $view->action == 'add' && $view->id == $role['id'] ): ?>
		<h5><?php echo t('Add user') ?></h5>

		<form id="formUser<?php echo $role['id'] ?>" method="post" action="./?action=add&id=<?php echo $role['id'] ?>">
			<fieldset>
				<dl>
					<dt>
						<label for="user"><?php echo t('User') ?></label>
					</dt>
					<dd>
						<select name="user" id="user">
							<option value="1">Admin (test)</option>
						</select>
					</dd>
				</dl>
			</fieldset>
			<fieldset>
				<dl>
					<dt><br/></dt>
					<dd>
						<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

						<input type="submit" class="button" name="form-submit-2" id="form-submit-2" value="<?php echo t('Add user') ?>"/>

						<a href="./"><?php echo t('Cancel') ?></a>
					</dd>
				</dl>
			</fieldset>
		</form>
		<?php endif ?>
		
		<h5><?php echo t('Users') ?></h5>

		<p>
			<a href="./?id=<?php echo $role['id'] ?>&action=add"><?php echo $model->t('Add a user') ?></a>
		</p>

		<?php if ( $role['users'] ): ?>
		<ul>
			<?php foreach ( $role['users'] as $user ): ?>
			<li>
				[ <a href=".?id=<?php echo $role['id'] ?>&user_id=<?php echo $user['id'] ?>&action=remove"><?php echo t('Remove') ?></a> ]
				<?php echo $user['username'] ?>
			</li>
			<?php endforeach ?>
		</ul>
		<?php else: ?>
		<p>
			<em><?php echo t('This role has no users') ?></em>
		</p>
		<?php endif ?>
	</li>
</ul>

<?php endforeach ?>
<?php endif ?>

<h2><?php echo t('Permissions') ?></h2>

<form id="formPerm" method="post" action="./">
	<fieldset>
		<table>
			<thead>
				<tr>
					<th><?php t('Permission') ?></th>
					<?php foreach ( $view->roles as $role ): ?>
					<th><?php echo h(t($role['name'])) ?></th>
					<?php endforeach ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $view->perms as $perm ): ?>
				<tr>
					<th>
						<?php echo h(t($perm['desc'])) ?>
					</th>
					<?php foreach ( $view->roles as $role ): ?>
					<td>
						<select name="value[<?php echo $perm['id'] ?>][<?php echo $role['id'] ?>]" id="value_<?php echo $perm['id'] ?>_<?php echo $role['id'] ?>">
							<option value="<?php echo perm::yes   ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::yes   ? ' selected="selected"' : '' ?>>&#10004; <?php echo t('Yes')   ?></option>
							<option value="<?php echo perm::no    ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::no    ? ' selected="selected"' : '' ?>>&#10008; <?php echo t('No')    ?></option>
							<option value="<?php echo perm::never ?>"<?php echo $model->POST_html_safe['value'][$perm['id']][$role['id']] == perm::never ? ' selected="selected"' : '' ?>>&#10008; <?php echo t('Never') ?></option>
						</select>
					</td>
					<?php endforeach ?>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" class="button" name="form-submit-3" id="form-submit-3" value="<?php echo $model->t('Save permissions') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>