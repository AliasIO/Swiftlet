<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<form id="form-menu" method="post" action="">
		<fieldset>
			<dl>
				<dt>
					<label for="items"><?php echo $this->t('Items') ?></label>

					<em><?php echo $this->t('Paths or URLs, each on a separate line (e.g. Title|node/21 or http://example.com)') ?></em>
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

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Save menu') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
</div>
