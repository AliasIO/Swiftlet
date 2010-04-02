<?php if ( !empty($model->form->errors) ): ?>
<script type="text/javascript">
	<!-- /* <![CDATA[ */
	<?php foreach ( $model->form->errors as $k => $v ): ?>
	$('#<?php echo $model->h($k) ?>').addClass('field-error');
	<?php endforeach ?>
	/* ]]> */ -->
</script>
<?php endif ?>
