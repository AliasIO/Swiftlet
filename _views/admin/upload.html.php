<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<?php if ( $view->action != 'upload' ): ?>
	<p>
		<a class="button" href="<?php echo $view->route('admin/upload/form/?callback=' . $view->callback) ?>"><?php echo $view->t('Upload files') ?></a>
	</p>
	<?php endif ?>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<?php if ( isset($view->route['args'][0]) && $view->route['args'][0] == 'form' ): ?>
	<h2><?php echo $view->t('Upload files') ?></h2>

	<form id="formFile" method="post" action="<?php echo $view->route('admin/upload/?callback=' . $view->callback) ?>" enctype="multipart/form-data">
		<?php for ( $i = 0; $i < 5; $i ++ ): ?>
		<fieldset>
			<dl>
				<dt><label for="title_<?php echo $i ?>"><?php echo $view->t('Title') ?></label></dt>
				<dd>
					<input type="text" name="title[<?php echo $i ?>]" id="title_<?php echo $i ?>" value="<?php echo $app->input->POST_html_safe['title'][$i] ?>"/>

					<?php if ( isset($app->input->errors['title'][$i]) ): ?>
					<span class="error"><?php echo $app->input->errors['title'][$i] ?></span>
					<?php endif ?>
				</dd>
			</dl>
			<dl>
				<dt><label for="file_<?php echo $i ?>"><?php echo $view->t('File') ?></label></dt>
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

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $view->t('Upload files') ?>"/>

					<p>
						<a href="?callback=<?php echo $view->callback ?>"><?php echo $view->t('Cancel') ?></a>
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

	<h2><?php echo $view->t('All files') ?></h2>

	<?php if ( $view->files ): ?>

	<p>
		<?php echo $view->filesPagination['html'] ?>
	</p>

	<table>
		<thead>
			<tr>
				<th><?php echo $view->t('Thumbnail')   ?></th>
				<th><?php echo $view->t('Title')       ?></th>
				<th><?php echo $view->t('File type')   ?></th>
				<th><?php echo $view->t('File size')   ?></th>
				<th><?php echo $view->t('Dimensions')  ?></th>
				<th><?php echo $view->t('Uploaded on') ?></th>
				<th><?php echo $view->t('Action')      ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $view->files as $file ): ?>
			<tr>
				<td>
					<?php if ( $file['image'] ): ?>
					<a
						href="<?php echo $view->route('file/' . $file['id'] . $file['extension']) ?>"
						onclick="callback('<?php echo $view->route('file/' . $file['id'] . $file['extension']) ?>');"
						>
						<img src="<?php echo $view->route('file/thumb/' . $file['id'] . $file['extension']) ?>" width="120" height="120" alt="">
					</a>
					<?php endif ?>
				</td>
				<td>
					<a
						href="<?php echo $view->route('file/' . $file['id'] . $file['extension']) ?>"
						onclick="callback('<?php echo $view->route('file/' . $file['id'] . $file['extension']) ?>');"
						>
						<?php echo $file['title'] ?>
					</a>
				</td>
				<td><?php echo $view->h($file['mime_type'] . ' (' . ltrim(strtoupper($file['extension']), '.') . ')') ?></td>
				<td><?php echo $file['size'] ? number_format($file['size'] / 1024, 0) . ' kB' : '' ?></td>
				<td><?php echo $file['width'] && $file['height'] ? ( int ) $file['width'] . 'x' . ( int ) $file['height'] : $view->t('n/a') ?></td>
				<td><?php echo $view->format_date($file['date'], 'date') ?></td>
				<td>
					<a class="button caution" href="<?php echo $view->route('admin/upload/delete/' . $file['id'] . '?callback=' . $view->callback) ?>"><?php echo $view->t('Delete') ?></a>
				</td>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<p>
		<?php echo $view->filesPagination['html'] ?>
	</p>
	<?php else: ?>
	<p>
		<em><?php echo $view->t('No files') ?></em>
	</p>
	<?php endif ?>

	<?php if ( $view->callback ): ?>
	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		var validCallback = window.opener && typeof(window.opener.<?php echo $view->callback ?>) != 'undefined';

		if ( validCallback ) {
			$('html, body').css({
				height:    '100%',
				overflowY: 'scroll'
				});
		}

		function callback(url) {
			url = url.replace(/^<?php echo preg_quote($view->rootPath, '/') ?>/, '');

			if ( validCallback ) {
				window.opener.<?php echo $view->callback ?>(url);

				window.close();

				return false;
			}
		}
		/* ]]> */ -->
	</script>
	<?php endif ?>
</div>
