<?php if ( !isset($this) ) die('Direct access to this file is not allowed') ?>

<?php if ( !empty($app->input->errors) ): ?>
<script type="text/javascript">
	<!-- /* <![CDATA[ */
	<?php foreach ( $app->input->errors as $k => $v ): ?>
	$('#<?php echo $this->h($k) ?>').addClass('field-error');
	<?php endforeach ?>
	/* ]]> */ -->
</script>
<?php endif ?>
