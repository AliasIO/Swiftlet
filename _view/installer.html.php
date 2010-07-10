<div class="no-grid">
	<h1><?php echo $model->t($contr->pageTitle) ?></h1>

	<?php if ( !empty($view->error) ): ?>
	<p class="message error"><?php echo $view->error ?></p>
	<?php endif ?>

	<?php if ( !empty($view->notice) ): ?>
	<p class="message notice"><?php echo $view->notice ?></p>
	<?php endif ?>

	<?php if ( $view->authenticated ): ?>
	<p>
		<?php echo $model->t('Select the plugins you wish to install, upgrade or remove. The system password is stored in %1$s.', '<code>/_config.php</code>') ?>
	</p>

	<h2><?php echo $model->t('Install') ?></h2>

	<?php if ( $view->newPlugins ): ?>
	<form id="formInstaller" method="post" action="./">
		<fieldset>
			<?php foreach ( $view->newPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?> (<?php echo $v['file'] ?>)
						<em>v<?php echo $v['version'] ?></em>
						<em><?php echo $v['description'] ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !in_array(0, $v['dependency_status']) ? ' checked="checked"' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $v['dependency_status'] ): ?>
					<em>
						<?php echo $model->t('Depends on') ?>:
						
						<?php foreach ( $v['dependency_status'] as $dependency => $ready ): ?>
						<?php echo ( $ready ? '<span class="dependency-ok" title="' . $model->t('Active') . '">' . $dependency . ' &#10004;</span>' : '<span class="dependency-fail" title="' . $model->t('Not active') . '">' . $dependency . ' &#10008;</span>' ) . '&nbsp;' ?>
						<?php endforeach ?>
					</em>
					<?php endif ?>
				</dd>
			</dl>
			<?php endforeach; ?>
		</fieldset>
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $model->t('System password') ?></label>
				</dt>
				<dd>
					<input type="password" name="system-password" id="system-password-1"/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="mode" value="install"/>

					<input type="hidden" name="auth-token" value="<?php echo $model->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Install') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>

	<script type="text/javascript">
		<!-- /* <![CDATA[ */
		// Focus the username field
		$('#system-password-1').focus();
		/* ]]> */ -->
	</script>
	<?php else: ?>
	<p>
		<em><?php echo $model->t('There are no plugins to be installed.') ?></em>
	</p>
	<?php endif ?>

	<h2><?php echo $model->t('Upgrade') ?></h2>

	<?php if ( $view->outdatedPlugins ): ?>
	<form id="formLogin" method="post" action="./">
		<fieldset>
			<?php foreach ( $view->outdatedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?>
						<em>v<?php echo $v['version'] ?></em>
					</label>
				</dt>
				<dd>
					<?php if ( $v['upgradable'] ): ?>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"/>
					<em>(<a href="javascript: void(0);" onclick="
						e = document.getElementById('sql_<?php echo $plugin ?>');
						e.style.display = e.style.display == 'none' ? 'block' : 'none';
						"><?php echo $model->t('View SQL') ?></a>)</em>
					<?php else: ?>
					<em><?php echo $model->t('No upgrade available from version') ?> <?php echo $v['installed_version'] ?></em>
					<?php endif; ?>
				</dd>
			</dl>
			<p id="sql_<?php echo $plugin ?>" style="display: none;"><code><?php echo $v['sql'] ?></code></p>
			<?php endforeach; ?>
		</fieldset>
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $model->t('System password') ?></label>
				</dt>
				<dd>
					<input type="password" name="system-password" id="system-password-2"/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="mode" value="upgrade"/>

					<input type="hidden" name="auth-token" value="<?php echo $model->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Upgrade') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $model->t('There are no plugins to be upgraded.') ?></em>
	</p>
	<?php endif ?>

	<h2><?php echo $model->t('Remove') ?></h2>

	<?php if ( $view->installedPlugins ): ?>
	<p>
		<?php echo $model->t('Removing a plugin will <em>permanently remove all data</em> associated with it. Backup your database first!') ?>
	</p>

	<form id="formInstaller" method="post" action="./">
		<fieldset>
			<?php foreach ( $view->installedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?> (<?php echo $v['file'] ?>)
						<em>v<?php echo $v['version'] ?></em>
						<em><?php echo $v['description'] ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !in_array(1, $v['required_by_status']) ? '' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $v['required_by_status'] ): ?>
					<em>
						<?php echo $model->t('Required by') ?>:

						<?php foreach ( $v['required_by_status'] as $requiredBy => $ready ): ?>
						<?php echo ( $ready ? '<span class="dependency-ok" title="' . $model->t('Active') . '">' . $requiredBy . ' &#10004;</span>' : '<span class="dependency-fail" title="' . $model->t('Not active') . '">' . $requiredBy . ' &#10008;</span>' ) . '&nbsp;' ?>
						<?php endforeach ?>
					</em>
					<?php endif ?>
				</dd>
			</dl>
			<?php endforeach; ?>
		</fieldset>
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $model->t('System password') ?></label>
				</dt>
				<dd>
					<input type="password" name="system-password" id="system-password-3"/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="mode" value="remove"/>

					<input type="hidden" name="auth-token" value="<?php echo $model->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Remove') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $model->t('There are no plugins to be removed.') ?></em>
	</p>
	<?php endif ?>

	<?php else: ?>
	<p>
		<?php echo $model->t('Please authenticate with the system password (stored in %1$s).', '<code>/_config.php</code>') ?>
	</p>

	<form id="formLogin" method="post" action="./">
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $model->t('system password') ?></label>
				</dt>
				<dd>
					<input type="password" name="system-password" id="system-password-3"/>
				</dd>
			</dl>
		</fieldset>
		<fieldset>
			<dl>
				<dt><br/></dt>
				<dd>
					<input type="hidden" name="mode" value="authenticate"/>

					<input type="hidden" name="auth-token" value="<?php echo $model->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $model->t('Authenticate') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php endif ?>
</div>
