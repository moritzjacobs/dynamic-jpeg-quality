<div class="wrap">
	<h2><?=$title?></h2>
	
	<?php if(!empty($msg)): ?>
		<div id="message" class="updated"><?= $msg?></div>
	<?php endif; ?>
		
	
	<form method="POST" action="">
		<input type="hidden" name="update_settings" value="Y" />
		<h3>Quality Settings</h3>
		
		<div class="djq-settings" id="djq-settings">
			<div class="bound-control">
				<label for="djq-lower-bound"><strong><?=__("Lower bound") . "</strong><br>(" . __("default: 30") . ")";?></label>
				<input type="number" name="djq-lower-bound" value="<?=$djq_lower_bound?>"><br><br><br>
				<label for="djq-lower-bound"><strong><?=__("Upper bound") . "</strong><br>(" . __("default: 90") . ")";?></label>
				<input type="number" name="djq-upper-bound" value="<?=$djq_upper_bound?>">
			</div>
			<div class="size-scalers">
			<?php
				$all_sizes = $this::get_all_sizes();

				$mps = array();
				foreach($all_sizes as $name=>&$size) {
					$mp = $size["width"]*$size["height"];
					$mps[] = $mp;
					$size = array("name"=>$name, "width"=>$size["width"], "height"=>$size["height"], "mp"=>$mp);
				}
				usort($all_sizes, function($a, $b) {
					return $a['mp'] > $b['mp'] ? 1 : -1;
				});
			?>
			<?php foreach($all_sizes as $name=>$img_size): ?>
				<div class="size-scaler" data-aspect-ratio="<?= $img_size["width"] / $img_size["height"]; ?>" data-mp="<?= $img_size["mp"]?>">
					<span class="demo"></span><br>
					<?=$img_size["name"]?><br><?= $img_size["width"]; ?>×<?= $img_size["height"]; ?><br><span class="percent">—</span>
				</div>
			<?php endforeach; ?>
			
		</div>

		
		
		<p>
			<input type="submit" value="<?=__("Save")?>" class="button-primary"/>
		</p>
	</form>

	
</div>

