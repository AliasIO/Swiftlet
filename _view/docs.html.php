<div class="no-grid">
	<h1><?php echo $model->t($contr->pageTitle) ?></h1>
</div>

<div id="documentation">
	<div class="grid">
		<div class="span-9">
			<?php echo $view->contents ?>
		</div>
		
		<div class="span-3">
			<h2>Manual</h2>

			<ul>
				<li><a href="<?php echo $view->rootPath ?>docs/manual/install"   >Installation</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/manual/config"    >Configuration</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/manual/page"      >Creating pages</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/manual/form"      >Forms</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/manual/faq"       >FAQ</a></li>
			</ul>
			
			<h2>Concepts</h2>
			
			<ul>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/model"     >Model</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/view"      >Views</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/controller">Controllers</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/hook"      >Hooks</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/plugin"    >Plugins</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/concepts/unit_test" >Unit Tests</a></li>
			</ul>

			<h2>Plugins</h2>

			<ul>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/buffer"    >Buffer</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/cache"     >Cache</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/dashboard" >Dashboard</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/email"     >E-mail</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/file"      >File</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/form"      >Form</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/lang"      >Lang</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/log"       >Log</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/mysql"     >MySQL</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/node"      >Node</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/page"      >Page</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/permission">Permission</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/session"   >Session</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/time"      >Time</a></li>
				<li><a href="<?php echo $view->rootPath ?>docs/plugins/user"      >User</a></li>
			</ul>
		</div>
	</div>
</div>
