<?php if ( !empty($app->input->errors) ): ?>
<script type="text/javascript">
	<!-- /* <![CDATA[ */
	<?php foreach ( $app->input->errors as $k => $v ): ?>
	$('#<?php echo $view->h($k) ?>').addClass('field-error');
	<?php endforeach ?>
	/* ]]> */ -->
</script>
<?php endif ?>
