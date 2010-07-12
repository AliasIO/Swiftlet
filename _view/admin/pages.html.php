<div class="no-grid">
	<h1><?php echo $app->t($contr->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<?php if ( $view->action == 'edit' && $app->perm->check('admin page edit') ): ?>
	<h2><?php echo $app->t('Edit page') ?></h2>

	<p>
		<?php if ( $app->perm->check('admin page create') ): ?>
		<a class="button" href="./"><?php echo $app->t('Create a new page') ?></a>
		<?php endif ?>
		<a class="button" href="<?php echo $app->route($view->path) ?>"><?php echo $app->t('View this page') ?></a>
		<?php if ( $app->perm->check('admin page delete') ): ?>
		<a class="button caution" href="./?action=delete&id=<?php echo $view->id ?>"><?php echo $app->t('Delete this page') ?></a>
		<?php endif ?>
	</p>
	<?php elseif ( $app->perm->check('admin page create') ): ?>
	<h2><?php echo $app->t('New page') ?></h2>
	<?php endif ?>

	<?php if ( $app->perm->check('admin page create') || $view->action == 'edit' && $app->perm->check('admin page edit') ): ?>

	<form id="formPage" method="post" action="./<?php echo $view->id ? '?action=edit&id=' . $view->id : '' ?>">
		<?php foreach ( $view->languages as $i => $language ): ?>
		<fieldset>
			<dl>
				<strong><?php echo $app->t($language) ?></strong>
			</dl>
			<dl>
				<dt><label for="title_<?php echo $i ?>"><?php echo $app->t('Title') ?></label></dt>
				<dd>
					<input type="text" name="title[<?php echo $app->h($language) ?>]" id="title_<?php echo $i ?>" value="<?php echo $app->POST_html_safe['title'][$language] ?>"/>
					
					<?php if ( isset($app->form->errors['title_' . $i]) ): ?>
					<span class="error"><?php echo $app->form->errors['title_' . $i] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="body<?php echo $i ?>"><?php echo $app->t('Body') ?></label></dt>
			</dl>
			<dl>
				<textarea class="large code ckeditor" name="body[<?php echo $app->h($language) ?>]" id="body_<?php echo $i ?>" cols="25" rows="5"><?php echo $app->POST_html_safe['body'][$language] ?></textarea>
			</dl>
		</fieldset>
		<?php endforeach ?>
		<fieldset>
			<dl>
				<dt><label for="published"><?php echo $app->t('Published') ?></label></dt>
				<dd>
					<input type="checkbox" name="published" id="published" value="1"<?php echo $app->POST_html_safe['published'] ? ' checked="checked"' : '' ?>/>
				</dd>
			</dl>
			<dl>
				<dt><label for="parent"><?php echo $app->t('Parent page') ?></label></dt>
				<dd>
					<select class="select" name="parent" id="parent">
						<option value="<?php echo node::rootId ?>"<?php echo $app->POST_html_safe['parent'] == node::rootId ? ' selected="selected"' : '' ?>><?php echo $app->t('None') ?></option>
						<?php foreach ( $view->nodesParents as $node ): ?>
						<option value="<?php echo $node['id'] ?>"<?php echo $app->POST_html_safe['parent'] == $node['id'] ? ' selected="selected"' : '' ?>><?php echo str_repeat('&nbsp;', $node['level'] * 3) . $node['title'] ?></option>
						<?php endforeach ?>
					</select>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><label for="home"><?php echo $app->t('Set as home page') ?></label></dt>
				<dd>
					<input type="checkbox" name="home" id="home" value="1"<?php echo $app->POST_html_safe['home'] ? ' checked="checked"' : '' ?>/>
				</dd>
			</dl>
			<dl>
				<dt>
					<label for="path"><?php echo $app->t('Custom path') ?></label>
					<em>E.g. "some/path"</em></dt>
				</dt>
				<dd>
					<input type="text" name="path" id="path" value="<?php echo $app->POST_html_safe['path'] ?>"/>
					
					<?php if ( isset($app->form->errors['path']) ): ?>
					<span class="error"><?php echo $app->form->errors['path'] ?></span>
					<?php endif ?>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $app->t('Save page') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php endif ?>

	<a name="pages"></a>

	<h2><?php echo $app->t('All pages') ?></h2>

	<?php if ( $view->nodes ): ?>
	<p class="pagination">
		<?php echo $view->nodesPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $app->t('Title') ?></th>
				<th><?php echo $app->t('Created on') ?></th>
				<th><?php echo $app->t('Action') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $view->nodes as $node ): ?>
			<tr>
				<td>
					<?php echo str_repeat('&nbsp;', $node['level'] * 4) ?>
					<a href="<?php echo $app->route($node['path'] ? $node['path'] : 'node/' . $node['id']) ?>">
						<?php echo $node['title'] ?>
					</a>
				</td>
				<td>
					<?php echo $app->format_date($node['date'], 'date') ?>
				</td>
				<td>
					<?php if ( $app->perm->check('admin page edit') ): ?>
					<a class="button" href="?id=<?php echo $node['id'] ?>&amp;action=edit"><?php echo $app->t('Edit') ?></a>
					<?php endif ?>
					<?php if ( $app->perm->check('admin page delete') ): ?>
					<a class="button caution" href="?id=<?php echo $node['id'] ?>&amp;action=delete"><?php echo $app->t('Delete') ?></a>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p class="pagination">
		<?php echo $view->nodesPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $app->t('No pages') ?></em>
	</p>
	<?php endif ?>

	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the title field
		$('#title').focus();
		/* ]]> */ -->
	</script>
</div>
