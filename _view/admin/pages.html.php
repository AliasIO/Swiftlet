<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'edit' && $model->perm->check('admin page edit') ): ?>
<h2><?php echo $model->t('Edit page') ?></h2>

<p>
	<?php if ( $model->perm->check('admin page create') ): ?>
	<a href="./"><?php echo $model->t('Create a new page') ?></a> |
	<?php endif ?>
	<a href="<?php echo $view->rootPath . $model->rewrite_url('page/?permalink=' . $view->permalink) ?>"><?php echo $model->t('View this page') ?></a>
	<?php if ( $model->perm->check('admin page delete') ): ?>
	| <a href="./?action=delete&id=<?php echo $view->id ?>"><?php echo $model->t('Delete this page') ?></a>
<?php endif ?>
</p>
<?php elseif ( $model->perm->check('admin page create') ): ?>
<h2><?php echo $model->t('New page') ?></h2>
<?php endif ?>

<?php if ( $model->perm->check('admin page create') || $view->action == 'edit' && $model->perm->check('admin page edit') ): ?>

<form id="formPage" method="post" action="./<?php echo $view->id ? '?action=edit&id=' . $view->id : '' ?>">
	<?php foreach ( $view->languages as $i => $language ): ?>
	<fieldset>
		<dl>
			<strong><?php echo h(t($language)) ?></strong>
		</dl>
		<dl>
			<dt><label for="title_<?php echo $i ?>"><?php echo $model->t('Title') ?></label></dt>
			<dd>
				<input type="text" class="text" name="title[<?php echo h($language) ?>]" id="title_<?php echo $i ?>" value="<?php echo $model->POST_html_safe['title'][$language] ?>"/>
				<?php if ( isset($model->form->errors['title'][$language]) ): ?>
				<span class="error"><?php echo $model->form->errors['title'][$language] ?></span>
				<?php endif ?>
			</dd>
		</dl>
		<dl>
			<dt><label for="body_<?php echo $i ?>"><?php echo $model->t('Body') ?></label></dt>
		</dl>
		<dl>
			<textarea class="textarea large code ckeditor" name="body[<?php echo h($language) ?>]" id="body_<?php echo $i ?>" cols="25" rows="5"><?php echo $model->POST_html_safe['body'][$language] ?></textarea>
			<?php if ( isset($model->form->errors['body'][$language]) ): ?>
			<span class="error"><?php echo $model->form->errors['body'][$language] ?></span>
			<?php endif ?>
		</dl>
	</fieldset>
	<?php endforeach ?>
	<fieldset>
		<dl>
			<dt><label for="parent"><?php echo $model->t('Parent page') ?></label></dt>
			<dd>
				<select class="select" name="parent" id="parent">
					<?php foreach ( $view->nodesParents as $node ): ?>
					<option value="<?php echo $node['id'] ?>"<?php echo $model->POST_html_safe['parent'] == $node['id'] ? ' selected="selected"' : '' ?>><?php echo str_repeat('&nbsp;', $node['level'] * 3) . $node['title'] ?></option>
					<?php endforeach ?>
				</select>
			</dd>
		</dl>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" class="button" name="form-submit" id="form-submit" value="<?php echo $model->t('Save page') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>
<?php endif ?>

<?php if ( $model->perm->check('admin page edit') ): ?>
<h2><?php echo $model->t('Select a page') ?></h2>

<form id="formPages" method="get" action="./">
	<fieldset>
		<dl>
			<dt><label for="id"><?php echo $model->t('Page') ?></label></dt>
			<dd>
				<select name="id" id="id" onchange="if ( this.value ) document.getElementById('formPages').submit();">
					<option value=""><?php echo $model->t('Select&hellip') ?></option>
					<?php foreach ( $view->nodes as $node ): ?>
					<option value="<?php echo $node['id'] ?>"<?php echo $view->id == $node['id'] ? ' selected="selected"' : '' ?>><?php echo str_repeat('&nbsp;', $node['level'] * 3) . $node['title'] ?></option>
					<?php endforeach ?>
				</select>
			</dd>
		</dl>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="action" id="action" value="edit"/>

				<input type="submit" class="button" id="form-submit2" value="<?php echo $model->t('Ok') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>
<?php endif ?>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	// Focus the title field
	$(function() {
		$('#title').focus();
	});
	/* ]]> */ -->
</script>