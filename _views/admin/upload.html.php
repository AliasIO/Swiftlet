<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( $this->action != 'upload' && $this->app->permission->check('admin upload upload') ): ?>
	<p>
		<a class="button" href="<?php echo $this->route($this->path . '/form?callback=' . $this->callback) ?>"><?php echo $this->t('Upload files') ?></a>
	</p>
	<?php endif ?>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<?php if ( $this->action == 'form' && $this->app->permission->check('admin upload upload') ): ?>
	<h2><?php echo $this->t('Upload files') ?></h2>

	<form id="form-file" method="post" action="<?php echo $this->route($this->request . '?callback=' . $this->callback) ?>" enctype="multipart/form-data">
		<?php for ( $i = 0; $i < 5; $i ++ ): ?>
		<fieldset>
			<dl>
				<dt><label for="title_<?php echo $i ?>"><?php echo $this->t('Title') ?></label></dt>
				<dd>
					<input type="text" name="title[<?php echo $i ?>]" id="title_<?php echo $i ?>" value="<?php echo $app->input->POST_html_safe['title'][$i] ?>"/>

					<?php if ( isset($app->input->errors['title'][$i]) ): ?>
					<span class="error"><?php echo $app->input->errors['title'][$i] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="file_<?php echo $i ?>"><?php echo $this->t('File') ?></label></dt>
				<dd>
					<input type="file" name="file[<?php echo $i ?>]" id="file_<?php echo $i ?>"/>

					<?php if ( isset($app->input->errors['file'][$i]) ): ?>
					<span class="error"><?php echo $app->input->errors['file'][$i] ?></span>
					<?php endif ?>
				</dd>
			</dl>
		</fieldset>
		<?php endfor ?>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Upload files') ?>"/>

					<p>
						<a href="<?php echo $this->route($this->path . '?callback=' . $this->callback) ?>"><?php echo $this->t('Cancel') ?></a>
					</p>
				</dd>
			</dl>
		</fieldset>
	</form>

	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the title field
		$('#title_0').focus();
		/* ]]> */ -->
	</script>
	<?php endif ?>

	<a name="files"></a>

	<h2><?php echo $this->t('All files') ?></h2>

	<?php if ( $this->files ): ?>

	<p>
		<?php echo $this->filesPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $this->t('Thumbnail')   ?></th>
				<th><?php echo $this->t('Title')       ?></th>
				<th><?php echo $this->t('File type')   ?></th>
				<th><?php echo $this->t('File size')   ?></th>
				<th><?php echo $this->t('Dimensions')  ?></th>
				<th><?php echo $this->t('Uploaded on') ?></th>
				<th><?php echo $this->t('Action')      ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $this->files as $file ): ?>
			<tr>
				<td>
					<?php if ( $file['image'] ): ?>
					<a
						href="<?php echo $this->route('file/' . $file['id'] . $file['extension']) ?>"
						onclick="callback('<?php echo $this->route('file/' . $file['id'] . $file['extension']) ?>');"
						>
						<img src="<?php echo $this->route('file/thumb/' . $file['id'] . $file['extension']) ?>" width="120" height="120" alt="">
					</a>
					<?php endif ?>
				</td>
				<td>
					<a
						href="<?php echo $this->route('file/' . $file['id'] . $file['extension']) ?>"
						onclick="callback('<?php echo $this->route('file/' . $file['id'] . $file['extension']) ?>');"
						>
						<?php echo $file['title'] ?>
					</a>
				</td>
				<td><?php echo $this->h($file['mime_type'] . ' (' . ltrim(strtoupper($file['extension']), '.') . ')') ?></td>
				<td><?php echo $file['size'] ? number_format($file['size'] / 1024, 0) . ' kB' : '' ?></td>
				<td><?php echo $file['width'] && $file['height'] ? ( int ) $file['width'] . 'x' . ( int ) $file['height'] : $this->t('n/a') ?></td>
				<td><?php echo $this->format_date($file['date'], 'date') ?></td>
				<td>
					<?php if ( $this->app->permission->check('admin upload delete') ): ?>
					<a class="button caution" href="<?php echo $this->route($this->path . '/delete/' . $file['id'] . '?callback=' . $this->callback) ?>"><?php echo $this->t('Delete') ?></a>
					<?php endif ?>
				</td>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p>
		<?php echo $this->filesPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('No files') ?></em>
	</p>
	<?php endif ?>

	<?php if ( $this->callback ): ?>
	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		var validCallback = window.opener && typeof(window.opener.<?php echo $this->callback ?>) != 'undefined';

		if ( validCallback ) {
			$('html, body').css({
				height:    '100%',
				overflowY: 'scroll'
				});
		}

		function callback(url) {
			url = url.replace(/^<?php echo preg_quote($this->rootPath, '/') ?>/, '');

			if ( validCallback ) {
				window.opener.<?php echo $this->callback ?>(url);

				window.close();

				return false;
			}
		}
		/* ]]> */ -->
	</script>
	<?php endif ?>
</div>
