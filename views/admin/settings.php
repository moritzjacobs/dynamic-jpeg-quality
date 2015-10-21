<div class="wrap">
	<h2><?=$title?></h2>
	
<?php
	
if (isset($_POST["update_settings"])) {
	
	$djq_lower_bound = esc_attr($_POST["djq-lower-bound"]);
	$djq_upper_bound = esc_attr($_POST["djq-upper-bound"]);
	update_option("djq_lower_bound", $djq_lower_bound);
	update_option("djq_upper_bound", $djq_upper_bound);
	
	echo '<div id="message" class="updated">'.__("Settings saved").'</div>';
}
	
$djq_lower_bound = !empty(get_option("djq_lower_bound")) ? get_option("djq_lower_bound") : 30;
$djq_upper_bound = !empty(get_option("djq_upper_bound")) ? get_option("djq_upper_bound") : 90;

?>
		
	
	<form method="POST" action="">
		<input type="hidden" name="update_settings" value="Y" />
		<h3>Quality Settings</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="djq-lower-bound"><?=__("Lower bound (default: 30)");?></label>
				</th>
				<td>
					<input name="djq-lower-bound" value="<?=$djq_lower_bound?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="djq-upper-bound"><?=__("Upper bound (default: 90)");?></label>
				</th>
				<td>
					<input name="djq-upper-bound" value="<?=$djq_upper_bound?>">
				</td>
			</tr>
		</table>
		<p>
			<input type="submit" value="<?=__("Save")?>" class="button-primary"/>
		</p>
	</form>

	
</div>

