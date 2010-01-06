<h1><?php echo t($contr->pageTitle) ?></h1>

<?php if ( !empty($view->error) ): ?>
<p class="message error"><?php echo $view->error ?></p>
<?php endif ?>

<?php if ( !empty($view->notice) ): ?>
<p class="message notice"><?php echo $view->notice ?></p>
<?php endif ?>

<h2><?php echo t('Uninstalled plug-ins') ?></h2>

<?php if ( !empty($view->install_notice) ): ?>
<p class="message notice"><?php echo $view->install_notice ?></p>
<?php endif ?>

<?php if ( $view->new_plugins ): ?>

<form id="formLogin" method="post" action="./">
	<fieldset>
		<?php foreach ( $view->new_plugins as $plugin => $v ): ?>
		<dl>
			<dt>
				<label for="plugin_<?php echo $plugin ?>">
					<?php echo $plugin ?>
					<em>v<?php echo $v['version'] ?></em>
					<em><?php echo $v['description'] ?></em>
				</label>
			</dt>
			<dd>
				<input type="checkbox" class="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !in_array(0, $v['is_ready']) ? '' : ' disabled="disabled"' ) ?>/>
				<em>(<a href="javascript: void(0);" onclick="
					e = document.getElementById('sql_<?php echo $plugin ?>');
					e.style.display = e.style.display == 'none' ? 'block' : 'none';
					"><?php echo t('View SQL') ?></a>)</em>
				<?php if ( $v['is_ready'] ): ?>
				<em><?php echo t('Depends on') ?>:
				<?php foreach ( $v['is_ready'] as $dependency => $ready ): ?>
				<?php echo ( $ready ? '<span class="dependency-ok" title="' . t('Ready') . '">' . $dependency . ' &#10004;</span>' : '<span class="dependency-fail" title="' . t('Not ready') . '">' . $dependency . ' &#10008;</span>' ) . '&nbsp;' ?>
				<?php endforeach ?>
				</em>
				<?php endif ?>
			</dd>
		</dl>
		<p id="sql_<?php echo $plugin ?>" style="display: none;"><code><?php echo $v['sql'] ?></code></p>
		<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<dl>
			<dt>
				<label for="system_password"><?php echo t('System password') ?></label>
			</dt>
			<dd>
				<input type="password" class="password" name="system_password" id="system_password"/>
			</dd>
		</dl>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="mode" value="install"/>

				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" class="button" name="form-submit" id="form-submit" value="<?php echo t('Install') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>

<?php endif ?>

<h2><?php echo t('Outdated plug-ins') ?></h2>

<?php if ( !empty($view->upgrade_notice) ): ?>
<p class="message notice"><?php echo $view->upgrade_notice ?></p>
<?php endif ?>

<?php if ( $view->outdated_plugins ): ?>

<form id="formLogin" method="post" action="./">
	<fieldset>
		<?php foreach ( $view->outdated_plugins as $plugin => $v ): ?>
		<dl>
			<dt>
				<label for="plugin_<?php echo $plugin ?>">
					<?php echo $plugin ?>
					<em>v<?php echo $v['version'] ?></em>
				</label>
			</dt>
			<dd>
				<?php if ( $v['upgradable'] ): ?>
				<input type="checkbox" class="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"/>
				<em>(<a href="javascript: void(0);" onclick="
					e = document.getElementById('sql_<?php echo $plugin ?>');
					e.style.display = e.style.display == 'none' ? 'block' : 'none';
					"><?php echo t('View SQL') ?></a>)</em>
				<?php else: ?>
				<em><?php echo t('No upgrade available from version') ?> <?php echo $v['installed_version'] ?></em>
				<?php endif; ?>
			</dd>
		</dl>
		<p id="sql_<?php echo $plugin ?>" style="display: none;"><code><?php echo $v['sql'] ?></code></p>
		<?php endforeach; ?>
	</fieldset>
	<fieldset>
		<dl>
			<dt>
				<label for="system_password"><?php echo t('System password') ?></label>
			</dt>
			<dd>
				<input type="password" class="password" name="system_password" id="system_password"/>
			</dd>
		</dl>
	</fieldset>
	<fieldset>
		<dl>
			<dt><br/></dt>
			<dd>
				<input type="hidden" name="mode" value="upgrade"/>

				<input type="hidden" name="auth_token" value="<?php echo $model->authToken ?>"/>

				<input type="submit" class="button" name="form-submit" id="form-submit" value="<?php echo t('Upgrade') ?>"/>
			</dd>
		</dl>
	</fieldset>
</form>

<?php endif ?>