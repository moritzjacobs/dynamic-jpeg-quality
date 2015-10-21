<div class="wrap">
	<h2><?=$title?></h2>
	
<?php
	
if (isset($_POST["update_settings"])) {
	$this->recalculate_all_attachments(true);
}
	
$djq_lower_bound = !empty(get_option("djq_lower_bound")) ? get_option("djq_lower_bound") : 30;
$djq_upper_bound = !empty(get_option("djq_upper_bound")) ? get_option("djq_upper_bound") : 90;

?>
		
	
	<form method="POST" action="">
		<input type="hidden" name="update_settings" value="Y" />
		<h3>Recalculate all JPEGS?</h3>
		<input class="djq_confirm button-primary" type="submit" value="<?=__("Recalculate")?>"/>
	</form>

	<script>
		jQuery(".djq_confirm").click(function(e){
			if(!confirm("<?=__("Sure?")?>")){ e.preventDefault() };
		});
	</script>

	
</div>

