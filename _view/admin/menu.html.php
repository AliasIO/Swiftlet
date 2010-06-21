<div class="no-grid">
	<h1><?php echo $model->t($contr->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<form id="formMenu" method="post" action="./">
		<fieldset>
			<dl>
				<dt><label for="items"><?php echo $model->t('Items') ?></label></dt>
				<dd>
					<textarea name="items" id="items" cols="25" rows="5"><?php echo $model->POST_html_safe['items'] ?></textarea>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $model->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Save page') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
</div>
