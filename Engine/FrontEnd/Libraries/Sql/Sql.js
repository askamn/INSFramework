jQuery(document).ready(function(){
    jQuery.noConflict();
    jQuery('#sql-begin').on("click",function(e){
        e.preventDefault();
        jQuery( "body" ).append( '<div class="infusionLoaderContainer"><div class="infusionLoader"></div></div>' );
        jQuery('.infusionLoaderContainer').fadeIn('slow');
        jQuery('.infusionLoaderContainer').css('display', 'flex');
        jQuery("#result").html('');

        var data = { 
            query: jQuery('#textarea').val(), 
            crumb: options.crumb 
        };
        Ajax( options, data, Success );
    }); 
});

function Success( result )
{
    jQuery('#result').html( result.message );   
    jQuery("#result").addClass('msg_notice');  
    jQuery('.infusionLoaderContainer').fadeOut('slow');
}   