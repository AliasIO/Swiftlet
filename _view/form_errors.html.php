<?php if ( !empty($app->form->errors) ): ?>
<script type="text/javascript">
	<!-- /* <![CDATA[ */
	<?php foreach ( $app->form->errors as $k => $v ): ?>
	$('#<?php echo $app->h($k) ?>').addClass('field-error');
	<?php endforeach ?>
	/* ]]> */ -->
</script>
<?php endif ?>
