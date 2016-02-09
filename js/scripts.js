/*!
 * dynamic-jpeg-quality, 
 * @version 0.0.1
 * @date 2016-02-09, 20:14
 */
!function(t){"use strict";t(document).ready(function(){var a=t("#djq-settings"),n=a.find(".size-scaler"),r=t("input[name=djq-upper-bound]"),e=t("input[name=djq-lower-bound]"),i=parseInt(n.eq(0).attr("data-mp")),s=0;n.each(function(){var a=parseInt(t(this).attr("data-aspect-ratio")),n=parseInt(t(this).attr("data-mp"));n>s&&(s=n),i>n&&(i=n);var r=Math.sqrt(n)/20;t(this).find(".demo").css({width:r*a,height:r})});var h=function(){var a=parseInt(r.val()),h=parseInt(e.val());n.each(function(){var n=parseInt(t(this).attr("data-mp")),r=1-(n-i)/s;r=Math.pow(r,2);var e=h+r*(a-h);e=Math.floor(e),t(this).find(".percent").text(e+"%")})};h();var d=function(){var a=parseInt(t(this).val());return 0>=a?(t(this).val(1),!1):a>100?(t(this).val(100),!1):void h()};r.change(d),e.change(d)})}(jQuery);