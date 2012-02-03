<?php namespace Swiftlet ?>

<?php require 'views/header.html.php' ?>

<h1><?php echo View::getTitle() ?></h1>

<p>
	<?php echo View::get('hello world') ?>
</p>

<?php require 'views/footer.html.php' ?>
