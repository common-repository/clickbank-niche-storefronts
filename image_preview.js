/*
 * Image preview script 
 * powered by jQuery (http://www.jquery.com)
 * 
 * written by Alen Grakalic (http://cssglobe.com)
 * 
 * for more info visit http://cssglobe.com/post/1695/easiest-tooltip-and-image-preview-using-jquery
 *
 */

var cns_heightCur = null,
    cns_yCur = null,
    yScreen = null,
    xOffset = 30,
    yOffset = -20,
    cns_curE = null;


function cns_show_preview(e)
{
    cns_curE = e;
    cns_yCur = e.clientY + yOffset;
    if (cns_heightCur) {
        var diff = cns_yCur + cns_heightCur + 60 - yScreen;
        if (diff > 0) cns_yCur = cns_yCur - diff;
        if (cns_yCur < 30) cns_yCur = 30;
        jQuery('#cns_preview')
            .css('top', cns_yCur + 'px')
            .css('left', (e.clientX + xOffset) + 'px');
    } else {
        jQuery('#cns_preview')
            .css('top', cns_yCur + 'px')
            .css('left', (e.clientX + xOffset) + 'px');
    }
}
 
cns_imagePreview = function(){
	/* CONFIG */
        xScreen = jQuery(window).width();
        yScreen = jQuery(window).height();
        cns_xPlace = null;
        cns_yPlace = null;
        cns_xOffset = null;
        cns_xSign = null;
		
		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	jQuery("a.cns_preview").hover(function(e){
		this.t = this.title;
		this.title = "";
		var c = (this.t != "") ? "<br />" + this.t : "";
        jQuery("body").append(
            '<p id="cns_preview">'
            + '<img src="'+cns_pluginURL+'/ajax-loader.gif" alt="Image preview" '
            + 'class="'+jQuery(this).attr('index')+'" />'+c+'</p>');
        
        var preload = new Image();
        preload.src = jQuery(this).attr('src');
        jQuery(preload).load(function() {
            jQuery('#cns_preview img')
                .attr('src', this.src)
                .load(function() {
                    cns_heightCur = jQuery(this).height();
                    if (cns_heightCur > yScreen - 50) {
                        cns_heightCur = yScreen - 100;
                        jQuery(this).height(cns_heightCur);
                    }
                    cns_show_preview(cns_curE);
                });
        });
        
        cns_heightCur = null;
        cns_yCur = e.clientY + yOffset;
        cns_curE = e;
		jQuery('#cns_preview')
            .css('top', cns_yCur + 'px')
            .css('left', (e.clientX + xOffset) + 'px')
            .fadeIn("fast");
    },
	function(){
		this.title = this.t;	
		jQuery("#cns_preview").remove();
    });
	jQuery("a.cns_preview").mousemove(function(e){
        cns_show_preview(e);
	});
};


// starting the script on page load
jQuery(document).ready(function(){
	cns_imagePreview();
});
