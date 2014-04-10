<?php include 'header.php' ?>

<h1><?= $this->pageTitle ?></h1>

<p>
	Arguments used to access this page:
</p>

<pre><?php print_r($this->arguments) ?></pre>

<?php include 'footer.php' ?>
