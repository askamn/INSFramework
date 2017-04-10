jQuery(document).ready(function(){
    jQuery('.login-button').on("click",function(e){
        e.preventDefault();
        Ins.loader( 'show' );

        var data = { 
            username: jQuery('.login-username').val(), 
            password: jQuery('.login-password').val(), 
            crumb: options.crumb 
        };
        Ajax( options, data, Validated );
    }); 
});

function Validated( result )
{
    if(result.statuscode == 1)
    {
        jQuery('.success span').html(options.successText);
        jQuery('.success').slideDown();
        Ins.loader( 'hide' );
        jQuery( "body" ).append( '<div class="infusionBannerContainer"><div class="infusionBanner"></div></div>' );
        jQuery('.infusionBannerContainer').fadeIn('slow');
        jQuery('.infusionBannerContainer').css('display', 'flex');
        window.location.replace( Ins.UrlDecode( jQuery( '.redirect' ).val() ) );
    }
    else
    {
        jQuery('.error span').html(options.errorText);
        jQuery('.error').slideDown();
        Ins.loader( 'hide' );
    }
}   