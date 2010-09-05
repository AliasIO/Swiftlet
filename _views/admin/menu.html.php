<div class="no-grid">
	<h1><?php echo $view->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<form id="formMenu" method="post" action="">
		<fieldset>
			<dl>
				<dt>
					<label for="items"><?php echo $view->t('Items') ?></label>

					<em>Paths or URLs, each on a separate line (e.g. Title|node/21 or http://example.com)</em>
				</dt>
				<dd>
					<textarea name="items" id="items" cols="25" rows="5"><?php echo $app->input->POST_html_safe['items'] ?></textarea>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $view->t('Save menu') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
</div>
