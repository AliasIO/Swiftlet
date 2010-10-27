<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<?php if ( $this->method == 'edit' && $app->permission->check('admin page edit') ): ?>
	<h2><?php echo $this->t('Edit page') ?></h2>

	<p>
		<?php if ( $app->permission->check('admin page create') ): ?>
		<a class="button" href="<?php echo $this->route('admin/page') ?>"><?php echo $this->t('Create a new page') ?></a>
		<?php endif ?>
		<a class="button" href="<?php echo $this->route($this->pagePath) ?>"><?php echo $this->t('View this page') ?></a>
		<?php if ( $app->permission->check('admin page delete') ): ?>
		<a class="button caution" href="<?php echo $this->route('admin/page/delete/' . $this->id) ?>"><?php echo $this->t('Delete this page') ?></a>
		<?php endif ?>
	</p>
	<?php elseif ( $app->permission->check('admin page create') ): ?>
	<h2><?php echo $this->t('New page') ?></h2>
	<?php endif ?>

	<?php if ( $app->permission->check('admin page create') || $this->method == 'edit' && $app->permission->check('admin page edit') ): ?>

	<form id="form-page" method="post" action="<?php echo $this->route($this->request) ?>">
		<?php foreach ( $this->languages as $i => $language ): ?>
		<fieldset>
			<dl>
				<strong><?php echo $this->t($language) ?></strong>
			</dl>
			<dl>
				<dt><label for="title_<?php echo $i ?>"><?php echo $this->t('Title') ?></label></dt>
				<dd>
					<input type="text" name="title[<?php echo $this->h($language) ?>]" id="title_<?php echo $i ?>" value="<?php echo $app->input->POST_html_safe['title'][$language] ?>"/>

					<?php if ( isset($app->input->errors['title_' . $i]) ): ?>
					<span class="error"><?php echo $app->input->errors['title_' . $i] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="body<?php echo $i ?>"><?php echo $this->t('Body') ?></label></dt>
			</dl>
			<dl>
				<textarea class="large code ckeditor" name="body[<?php echo $this->h($language) ?>]" id="body_<?php echo $i ?>" cols="25" rows="5"><?php echo $app->input->POST_html_safe['body'][$language] ?></textarea>
			</dl>
		</fieldset>
		<?php endforeach ?>
		<fieldset>
			<dl>
				<dt><label for="published"><?php echo $this->t('Published') ?></label></dt>
				<dd>
					<input type="checkbox" name="published" id="published" value="1"<?php echo $app->input->POST_html_safe['published'] ? ' checked="checked"' : '' ?>/>
				</dd>
			</dl>
			<dl>
				<dt><label for="parent"><?php echo $this->t('Parent page') ?></label></dt>
				<dd>
					<select class="select" name="parent" id="parent">
						<option value="<?php echo Node_Plugin::ROOT_ID ?>"<?php echo $app->input->POST_html_safe['parent'] == Node_Plugin::ROOT_ID ? ' selected="selected"' : '' ?>><?php echo $this->t('None') ?></option>
						<?php foreach ( $this->nodesParents as $node ): ?>
						<option value="<?php echo $node['id'] ?>"<?php echo $app->input->POST_html_safe['parent'] == $node['id'] ? ' selected="selected"' : '' ?>><?php echo str_repeat('&nbsp;', $node['level'] * 3) . $node['title'] ?></option>
						<?php endforeach ?>
					</select>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><label for="home"><?php echo $this->t('Set as home page') ?></label></dt>
				<dd>
					<input type="checkbox" name="home" id="home" value="1"<?php echo $app->input->POST_html_safe['home'] ? ' checked="checked"' : '' ?>/>
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="path"><?php echo $this->t('Custom path') ?></label>
					<em>E.g. "some/path"</em></dt>
				</dt>
				<dd>
					<input type="text" name="path" id="path" value="<?php echo $app->input->POST_html_safe['path'] ?>"/>

					<?php if ( isset($app->input->errors['path']) ): ?>
					<span class="error"><?php echo $app->input->errors['path'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Save page') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php endif ?>

	<a name="pages"></a>

	<h2><?php echo $this->t('All pages') ?></h2>

	<?php if ( $this->nodes ): ?>
	<p class="pagination">
		<?php echo $this->nodesPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $this->t('Title') ?></th>
				<th><?php echo $this->t('Created on') ?></th>
				<th><?php echo $this->t('Action') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $this->nodes as $node ): ?>
			<tr>
				<td>
					<?php echo str_repeat('&nbsp;', $node['level'] * 4) ?>
					<a href="<?php echo $this->route($node['path'] ? $node['path'] : 'node/' . $node['id']) ?>">
						<?php echo $node['title'] ?>
					</a>
				</td>
				<td>
					<?php echo $this->format_date($node['date'], 'date') ?>
				</td>
				<td>
					<?php if ( $app->permission->check('admin page edit') ): ?>
					<a class="button" href="<?php echo $this->route('admin/page/edit/' . $node['id']) ?>"><?php echo $this->t('Edit') ?></a>
					<?php endif ?>
					<?php if ( $app->permission->check('admin page delete') ): ?>
					<a class="button caution" href="<?php echo $this->route('admin/page/delete/' . $node['id']) ?>"><?php echo $this->t('Delete') ?></a>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p class="pagination">
		<?php echo $this->nodesPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('No pages') ?></em>
	</p>
	<?php endif ?>

	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the title field
		$('#title').focus();
		/* ]]> */ -->
	</script>
</div>
