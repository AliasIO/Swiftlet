<?php include 'header.php' ?>

<h1><?= $this->pageTitle ?></h1>

<p>
	<?= $this->helloWorld ?>
	<br />
	Welcome to the <strong><?= basename(dirname(__FILE__)) ?></strong> directory.
</p>

<?php include 'footer.php' ?>
