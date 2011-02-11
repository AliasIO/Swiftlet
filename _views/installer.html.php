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
		<?php echo $this->t('Select the plugins you wish to install, upgrade or uninstall. The system password is stored in %1$s.', '<code>/_config.php</code>') ?>
	</p>

	<h2><?php echo $this->t('Install') ?></h2>

	<?php if ( $this->newPlugins ): ?>
	<form id="form-install" method="post" action="<?php echo $this->route('installer') ?>">
		<fieldset>
			<?php foreach ( $this->newPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?>
						<em>v<?php echo $v->version     ?></em>
						<em><?php  echo $v->description ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !$this->app->{$plugin}->dependencyStatus['missing'] ? ' checked="checked"' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $this->app->{$plugin}->dependencyStatus['missing'] ): ?>
					<em class="installer-warning">
						<?php echo $this->t('Missing dependencies') ?>:

						<?php echo implode(', ', $this->app->{$plugin}->dependencyStatus['missing']) ?>
					</em>
					<?php elseif ( $this->app->{$plugin}->dependencyStatus['installable'] ): ?>
					<em>
						<?php echo $this->t('Also enable') ?>:

						<?php echo implode(', ', $this->app->{$plugin}->dependencyStatus['installable']) ?>
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
	<form id="form-upgrade" method="post" action="<?php echo $this->route('installer') ?>">
		<fieldset>
			<?php foreach ( $this->outdatedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?>
						<em>v<?php echo $v->version     ?></em>
						<em><?php  echo $v->description ?></em>
					</label>
				</dt>
				<dd>
					<?php if ( $v->upgradable ): ?>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !$this->app->{$plugin}->dependencyStatus['missing'] ? ' checked="checked"' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $this->app->{$plugin}->dependencyStatus['missing'] ): ?>
					<em>
						<?php echo $this->t('Missing dependencies') ?>:

						<span class="installer-warning"><?php echo implode(', ', $this->app->{$plugin}->dependencyStatus['missing']) ?></span>
					</em>
					<?php endif ?>
					<?php else: ?>
					<em class="installer-warning"><?php echo $this->t('Unable to upgrade from currently installed version.') ?></em>
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

	<h2><?php echo $this->t('Uninstall') ?></h2>

	<?php if ( $this->installedPlugins ): ?>
	<p>
		<?php echo $this->t('Uninstalling a plugin will also %1$spermanently remove all data%2$s associated with it. Backup your database first!', array('<em>', '</em>')) ?>
	</p>

	<form id="form-uninstall" method="post" action="">
		<fieldset>
			<?php foreach ( $this->installedPlugins as $plugin => $v ): ?>
			<dl>
				<dt>
					<label for="plugin_<?php echo $plugin ?>">
						<?php echo $plugin ?>
						<em>v<?php echo $v->version     ?></em>
						<em><?php echo  $v->description ?></em>
					</label>
				</dt>
				<dd>
					<input type="checkbox" name="plugin[<?php echo $plugin ?>]" id="plugin_<?php echo $plugin ?>"<?php echo ( !$this->app->{$plugin}->dependencyStatus['required by'] ? '' : ' disabled="disabled" style="visibility: hidden"' ) ?>/>

					<?php if ( $this->app->{$plugin}->dependencyStatus['required by'] ): ?>
					<em class="installer-warning">
						<?php echo $this->t('Required by') ?>:

						<?php echo implode(', ', $this->app->{$plugin}->dependencyStatus['required by']) ?>
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

					<input type="submit" name="form-submit" id="form-submit" value="<?php echo $this->t('Uninstall') ?>"/>
				</dd>
			</dl>
		</fieldset>
	</form>
	<?php else: ?>
	<p>
		<em><?php echo $this->t('There are no plugins to be uninstalled.') ?></em>
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
