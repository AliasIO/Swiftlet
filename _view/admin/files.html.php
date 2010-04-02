<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( $view->action != 'upload' ): ?>
<p>
	<a class="button" href="?action=upload"><?php echo $model->t('Upload files') ?></a>
</p>
<?php endif ?>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'upload' ): ?>
<h2><?php echo $model->t('Upload files') ?></h2>

<form id="formFile" method="post" action="./?action=upload" enctype="multipart/form-data">
	<?php for ( $i = 0; $i < 5; $i ++ ): ?>
	<fieldset>
		<dl>
			<dt><label for="title_<?php echo $i ?>"><?php echo $model->t('Title') ?></label></dt>
			<dd>
				<input type="text" name="title[<?php echo $i ?>]" id="title_<?php echo $i ?>" value="<?php echo $model->POST_html_safe['title'][$i] ?>"/>
				
				<?php if ( isset($model->form->errors['title'][$i]) ): ?>
				<span class="error"><?php echo $model->form->errors['title'][$i] ?></span>
				<?php endif ?>
			</dd>
		</dl>
		<dl>
			<dt><label for="file_<?php echo $i ?>"><?php echo $model->t('File') ?></label></dt>
			<dd>
				<input type="file" name="file[<?php echo $i ?>]" id="file_<?php echo $i ?>"/>
				
				<?php if ( isset($model->form->errors['file'][$i]) ): ?>
				<span class="error"><?php echo $model->form->errors['file'][$i] ?></span>
				<?php endif ?>
			</dd>
		</dl>
	</fieldset>
	<?php endfor ?>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Upload files') ?>"/>

				<p>
					<a href="?"><?php echo $model->t('Cancel') ?></a>
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

<h2><?php echo $model->t('All files') ?></h2>

<?php if ( $view->files ): ?>

<p>
	<?php echo $view->filesPagination['html'] ?>
</p>

<table>
	<thead>
		<tr>
			<th><?php echo $model->t('Thumbnail')   ?></th>
			<th><?php echo $model->t('Title')       ?></th>
			<th><?php echo $model->t('File type')   ?></th>
			<th><?php echo $model->t('File size')   ?></th>
			<th><?php echo $model->t('Dimensions')  ?></th>
			<th><?php echo $model->t('Uploaded on') ?></th>
			<th><?php echo $model->t('Action')      ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $view->files as $file ): ?>
		<tr>
			<td>
				<?php if ( $file['image'] ): ?>
				<a
					href="<?php echo $model->route('file/' . $file['permalink'] . $file['extension']) ?>"
					onclick="if ( window.opener && typeof(window.opener.CKEDITOR) != 'undefined' ) window.opener.CKEDITOR.tools.callFunction(2, '<?php echo $model->route('file/' . $file['permalink'] . $file['extension']) ?>'); window.close();"
					>
					<img src="<?php echo $model->route('file/thumb/' . $file['permalink'] . $file['extension']) ?>" width="120" height="120" alt="">
				</a>
				<?php endif ?>
			</td>
			<td>
				<a
					href="<?php echo $model->route('file/' . $file['permalink'] . $file['extension']) ?>"
					onclick="if ( window.opener && typeof(window.opener.CKEDITOR) != 'undefined' ) window.opener.CKEDITOR.tools.callFunction(2, '<?php echo $model->route('file/' . $file['permalink'] . $file['extension']) ?>'); window.close();"
					>
					<?php echo $file['title'] ?>
				</a>
			</td>
			<td><?php echo $file['mime_type'] . ' (' . ltrim(strtoupper($file['extension']), '.') . ')' ?></td>
			<td><?php echo $file['size'] ? number_format($file['size'] / 1024, 0) . ' kB' : '' ?></td>
			<td><?php echo $file['width'] && $file['height'] ? $file['width'] . 'x' . $file['height'] : $model->t('n/a') ?></td>
			<td><?php echo $model->format_date($file['date'], 'date') ?></td>
			<td>
				<a class="button" href="?id=<?php echo $file['node_id'] ?>&action=delete"><?php echo $model->t('Delete') ?></a>
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
	<em><?php echo $model->t('No files') ?></em>
</p>
<?php endif ?>

<script type="text/javascript">
	<!-- /* <![CDATA[ */	
	if ( window.opener && typeof(window.opener.CKEDITOR) != 'undefined' ) {
		$('body').css({
			height:    '100%',
			overflowY: 'auto'
			});
	}
	/* ]]> */ -->
</script>
