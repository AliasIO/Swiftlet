<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( $view->action != 'upload' ): ?>
<p>
	<a href="?action=upload"><?php echo t('Upload files') ?></a>
</p>
<?php endif ?>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'upload' ): ?>
<h2><?php echo t('Upload files') ?></h2>

<form id="formFile" method="post" action="./?action=upload" enctype="multipart/form-data">
	<?php for ( $i = 0; $i < 5; $i ++ ): ?>
	<fieldset>
		<dl>
			<dt><label for="title_<?php echo $i ?>"><?php echo $model->t('Title') ?></label></dt>
			<dd>
				<input type="text" class="text" name="title[<?php echo $i ?>]" id="title_<?php echo $i ?>" value="<?php echo $model->POST_html_safe['title'][$i] ?>"/>
				
				<?php if ( isset($model->form->errors['title'][$i]) ): ?>
				<span class="error"><?php echo $model->form->errors['title'][$i] ?></span>
				<?php endif ?>
			</dd>
		</dl>
		<dl>
			<dt><label for="file_<?php echo $i ?>"><?php echo $model->t('File') ?></label></dt>
			<dd>
				<input type="file" class="file" name="file[<?php echo $i ?>]" id="file_<?php echo $i ?>"/>
				
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

				<input type="submit" class="button" name="form-submit" id="form-submit" value="<?php echo $model->t('Upload files') ?>"/>

				<p>
					<a href="?"><?php echo t('Cancel') ?></a>
				</p>
			</dd>
		</dl>
	</fieldset>
</form>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	// Focus the title field
	$(function() {
		$('#title_0').focus();
	});
	/* ]]> */ -->
</script>
<?php endif ?>

<h2>Files</h2>

<?php if ( $view->files ): ?>
<table>
	<thead>
		<tr>
			<th><?php echo t('Thumbnail')   ?></th>
			<th><?php echo t('Title')       ?></th>
			<th><?php echo t('File type')   ?></th>
			<th><?php echo t('File size')   ?></th>
			<th><?php echo t('Dimensions')  ?></th>
			<th><?php echo t('Uploaded on') ?></th>
			<th><?php echo t('Action')      ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $view->files as $file ): ?>
		<tr>
			<td>
				<?php if ( $file['image'] ): ?>
				<a
					href="<?php echo $model->rewrite_url($contr->rootPath . 'file/?name=' . $file['permalink'] . $file['extension']) ?>"
					onclick="if ( typeof(window.opener.CKEDITOR) != 'undefined' ) window.opener.CKEDITOR.tools.callFunction(2, '<?php echo $model->rewrite_url('file/?name=' . $file['permalink'] . $file['extension']) ?>'); window.close();"
					>
					<img src="<?php echo $model->rewrite_url($contr->rootPath . 'file/?name=thumb/' . $file['permalink'] . $file['extension']) ?>" width="120" height="120" alt="">
				</a>
				<?php endif ?>
			</td>
			<td>
				<a
					href="<?php echo $model->rewrite_url($contr->rootPath . 'file/?name=' . $file['permalink'] . $file['extension']) ?>"
					onclick="if ( typeof(window.opener.CKEDITOR) != 'undefined' ) window.opener.CKEDITOR.tools.callFunction(2, '<?php echo $model->rewrite_url('file/?name=' . $file['permalink'] . $file['extension']) ?>'); window.close();"
					>
					<?php echo $file['title'] ?>
				</a>
			</td>
			<td><?php echo $file['mime_type'] . ' (' . ltrim(strtoupper($file['extension']), '.') . ')' ?></td>
			<td><?php echo $file['size'] ? number_format($file['size'] / 1024, 0) . ' kB' : '' ?></td>
			<td><?php echo $file['width'] && $file['height'] ? $file['width'] . 'x' . $file['height'] : t('n/a') ?></td>
			<td><?php echo $model->format_date($file['date'], 'date') ?></td>
			<td>
				<a href="?id=<?php echo $file['node_id'] ?>&action=delete"><?php echo t('Delete') ?></a>
			</td>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php else: ?>
<p>
	<em><?php echo t('No files') ?></em>
</p>
<?php endif ?>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	$(function() {
		$('body').css({
			height:    '100%',
			overflowY: 'auto'
			});
	});
	/* ]]> */ -->
</script>

<?php if ( !empty($model->GET_raw['CKEditorFuncNum']) ): ?>
<?php endif ?>