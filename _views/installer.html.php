<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->t($controller->pageTitle) ?></h1>

	<?php if ( !empty($this->error) ): ?>
	<p class="message error"><?php echo $this->error ?></p>
	<?php endif ?>

	<?php if ( !empty($this->notice) ): ?>
	<p class="message notice"><?php echo $this->notice ?></p>
	<?php endif ?>

	<?php if ( $this->authenticated ): ?>
	<p>
		<?php echo $this->t('Select the plugins you wish to install, upgrade or remove. The system password is stored in %1$s.', '<code>/_config.php</code>') ?>
	</p>

	<h2><?php echo $this->t('Install') ?></h2>

	<?php if ( $this->newPlugins ): ?>
	<form id="form-install" method="post" action="<?php echo $this->route('installer') ?>">
		<fieldset>
			<?php foreach ( $this->newPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?> (<?php echo $v->file ?>)
						<em>v<?php echo $v->version ?></em>
						<em><?php echo $v->description ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !in_array(0, $v->dependency_status) ? ' checked="checked"' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $v->dependency_status ): ?>
					<em>
						<?php echo $this->t('Depends on') ?>:

						<?php foreach ( $v->dependency_status as $dependency => $ready ): ?>
						<?php echo ( $ready ? '<span class="dependency-ok" title="' . $this->t('Active') . '">' . $dependency . ' &#10004;</span>' : '<span class="dependency-fail" title="' . $this->t('Not active') . '">' . $dependency . ' &#10008;</span>' ) . '&nbsp;' ?>
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
					<label for="system_password"><?php echo $this->t('System password') ?></label>
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

					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Install') ?>"/>
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
		<em><?php echo $this->t('There are no plugins to be installed.') ?></em>
	</p>
	<?php endif ?>

	<h2><?php echo $this->t('Upgrade') ?></h2>

	<?php if ( $this->outdatedPlugins ): ?>
	<form id="form-upgrade" method="post" action="">
		<fieldset>
			<?php foreach ( $this->outdatedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?>
						<em>v<?php echo $v->version ?></em>
					</label>
				</dt>
				<dd>
					<?php if ( $v['upgradable'] ): ?>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"/>
					<em>(<a href="javascript: void(0);" onclick="
						e = document.getElementById('sql_<?php echo $plugin ?>');
						e.style.display = e.style.display == 'none' ? 'block' : 'none';
						"><?php echo $this->t('View SQL') ?></a>)</em>
					<?php else: ?>
					<em><?php echo $this->t('No upgrade available from version') ?> <?php echo $v['installed_version'] ?></em>
					<?php endif; ?>
				</dd>
			</dl>
			<p id="sql_<?php echo $plugin ?>" style="display: none;"><code><?php echo $v['sql'] ?></code></p>
			<?php endforeach; ?>
		</fieldset>
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $this->t('System password') ?></label>
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

					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Upgrade') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('There are no plugins to be upgraded.') ?></em>
	</p>
	<?php endif ?>

	<h2><?php echo $this->t('Remove') ?></h2>

	<?php if ( $this->installedPlugins ): ?>
	<p>
		<?php echo $this->t('Removing a plugin will also %1$spermanently remove all data%2$s associated with it. Backup your database first!', array('<em>', '</em>')) ?>
	</p>

	<form id="form-remove" method="post" action="">
		<fieldset>
			<?php foreach ( $this->installedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?> (<?php echo $v->file ?>)
						<em>v<?php echo $v->version ?></em>
						<em><?php echo $v->description ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !in_array(1, $v->required_by_status) ? '' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $v->required_by_status ): ?>
					<em>
						<?php echo $this->t('Required by') ?>:

						<?php foreach ( $v->required_by_status as $requiredBy => $ready ): ?>
						<?php echo ( $ready ? '<span class="dependency-ok" title="' . $this->t('Active') . '">' . $requiredBy . ' &#10004;</span>' : '<span class="dependency-fail" title="' . $this->t('Not active') . '">' . $requiredBy . ' &#10008;</span>' ) . '&nbsp;' ?>
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
					<label for="system_password"><?php echo $this->t('System password') ?></label>
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

					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Remove') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('There are no plugins to be removed.') ?></em>
	</p>
	<?php endif ?>

	<?php else: ?>
	<p>
		<?php echo $this->t('Please authenticate with the system password (stored in %1$s).', '<code>/_config.php</code>') ?>
	</p>

	<form id="formLogin" method="post" action="">
		<fieldset>
			<dl>
				<dt>
					<label for="system_password"><?php echo $this->t('system password') ?></label>
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

					<input type="hidden" name="auth-token" value="<?php echo $app->input->authToken ?>"/>

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Authenticate') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php endif ?>
</div>
