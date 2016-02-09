(function($){
	"use strict";
	
	$(document).ready(function(){
		var settings = $("#djq-settings");
		var scalers = settings.find(".size-scaler");
		var ub_input = $("input[name=djq-upper-bound]");
		var lb_input = $("input[name=djq-lower-bound]");
		
		var smallest_mp = parseInt(scalers.eq(0).attr("data-mp"));
		var biggest_mp = 0;
		
		scalers.each(function(){
			var ar = parseInt($(this).attr("data-aspect-ratio"));
			var mp = (parseInt($(this).attr("data-mp")));
			if (mp > biggest_mp) { biggest_mp = mp; }
			if (mp < smallest_mp) { smallest_mp = mp; }
			
			var display_size = Math.sqrt(mp) / 20;

			$(this).find(".demo").css({
				"width": (display_size*ar),
				"height": display_size,
			});
		});
		
		var calcSteps = function(){
			
			var ub = parseInt(ub_input.val());
			var lb = parseInt(lb_input.val());
			scalers.each(function(){
				var this_mp = parseInt($(this).attr("data-mp"));
				var scale_f = 1- ((this_mp - smallest_mp) / biggest_mp);
				scale_f = Math.pow(scale_f, 2);
				var q = lb + (scale_f * (ub-lb));
				q = Math.floor(q);
				$(this).find(".percent").text(q + "%");
				// quality in % => lb + (scale_f * (ub-lb));
			});
		};
		
		calcSteps();
		var onChange = function(){
			var val = parseInt($(this).val());
			if(val <= 0) {
				$(this).val(1);
				return false;
			} else if(val > 100) {
				$(this).val(100);
				return false;
			} else {
				calcSteps();
			}
		};
		
		ub_input.change(onChange);
		lb_input.change(onChange);

	});
})(jQuery);