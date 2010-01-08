<h1><?php echo $model->t($contr->pageTitle) ?></h1>

<?php if ( $view->action != 'upload' ): ?>
<p>
	<a href="?action=upload">Upload new files</a>
</p>
<?php endif ?>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<?php if ( $view->action == 'upload' ): ?>
<h2>Upload files</h2>

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
					<a href="?">Cancel</a>
				</p>
			</dd>
		</dl>
	</fieldset>
</form>
<?php endif ?>

<script type="text/javascript">
	<!-- /* <![CDATA[ */
	// Focus the title field
	$(function() {
		$('#title_0').focus();
	});
	/* ]]> */ -->
</script>

<h2>Files</h2>

<?php if ( $view->files ): ?>
<?php foreach ( $view->files as $file ): ?>
<?php echo print_r($file) ?>
<?php endforeach ?>
<?php else: ?>
<p>
	<em><?php echo t('No files') ?></em>
</p>
<?php endif ?>