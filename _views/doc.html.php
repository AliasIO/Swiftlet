<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<div class="no-grid">
	<h1><?php echo $this->t('Documentation') ?></h1>
</div>

<div id="documentation">
	<div class="grid">
		<div class="span-9">
			<?php echo $this->contents ?>
		</div>

		<div class="span-3">
			<h2>Guides</h2>

			<ul>
				<li><a href="<?php echo $this->route('doc/guides/install') ?>">Installation</a></li>
				<li><a href="<?php echo $this->route('doc/guides/config')  ?>">Configuration</a></li>
				<li><a href="<?php echo $this->route('doc/guides/page')    ?>">Creating pages</a></li>
				<li><a href="<?php echo $this->route('doc/guides/theme')   ?>">Theming</a></li>
				<li><a href="<?php echo $this->route('doc/guides/form')    ?>">Forms</a></li>
				<li><a href="<?php echo $this->route('doc/guides/routes')  ?>">Routes</a></li>
				<li><a href="<?php echo $this->route('doc/guides/plugin')  ?>">Creating plugins</a></li>
				<li><a href="<?php echo $this->route('doc/guides/ajax')    ?>">AJAX</a></li>
				<li><a href="<?php echo $this->route('doc/guides/faq')     ?>">FAQ</a></li>
			</ul>

			<h2>Concepts</h2>

			<ul>
				<li><a href="<?php echo $this->route('doc/concepts/application') ?>">Application</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/view')        ?>">Views</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/controller')  ?>">Controllers</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/hook')        ?>">Hooks</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/plugin')      ?>">Plugins</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/helper')      ?>">Helpers</a></li>
				<li><a href="<?php echo $this->route('doc/concepts/unit_test')   ?>">Unit Tests</a></li>
			</ul>

			<h2>Plugins</h2>

			<ul>
				<li><a href="<?php echo $this->route('doc/plugins/buffer')     ?>">Buffer</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/cache')      ?>">Cache</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/dashboard')  ?>">Dashboard</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/db')         ?>">Database</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/email')      ?>">E-mail</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/footer')     ?>">Footer</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/header')     ?>">Header</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/input')      ?>">Input</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/language')   ?>">Language</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/log')        ?>">Log</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/menu')       ?>">Menu</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/node')       ?>">Node</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/page')       ?>">Page</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/permission') ?>">Permission</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/upload')     ?>">Upload</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/session')    ?>">Session</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/time')       ?>">Time</a></li>
				<li><a href="<?php echo $this->route('doc/plugins/user')       ?>">User</a></li>
			</ul>
		</div>
	</div>
</div>
