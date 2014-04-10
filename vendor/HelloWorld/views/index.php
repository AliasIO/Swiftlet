<?php include 'header.php' ?>

<h1><?= $this->pageTitle ?></h1>

<p>
	<?= $this->helloWorld ?>
</p>

<p>
	<a href="<?= $this->getRootPath() ?>hello/world">Custom route example</a>
</p>

<?php include 'footer.php' ?>
