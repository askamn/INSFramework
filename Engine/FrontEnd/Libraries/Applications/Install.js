jQuery(document).ready(function(){
    jQuery.noConflict();
    /** 
     *   We don't use our generic ajax function, meh, 
     *   this one needs needs complete instead of success and also needs timeout var to be set
     */
    var ajaxInstall = function( url, baseUrl ) 
    {
        jQuery.ajax({
            url: url,
            dataType: 'json',
            timeout: 25000, /* Should be fine, or no? O.o */
            data: { crumb: options.crumb }, /* We must send our crumb, else, die(); */
            complete: function( xhr, textStatus )
            {
                var result = jQuery.parseJSON( xhr.responseText );

                /* Failed, oops */
                if( result.statuscode == '0' )
                {
                    Ins.GenericLoader("show");
                    Ins.BuildModal( result.message, result.modalheader, options.returnUrl + '&completed=installfailed', 10000 );
                }
                
                if( result.responseCode == '3' )
                {
                    Ins.GenericLoader("show");
                    Ins.BuildModal( result.message, null, options.returnUrl + '&completed=installfailed', 10000 );
                }
                else if( result.responseCode == '4' )
                {
                    Ins.GenericLoader("show");
                    Ins.BuildModal( result.message, null, options.returnUrl + '&completed=installfailed', 10000 );
                }
                else
                {
                    Ins.GenericLoader("show");
                    Ins.BuildModal( result.message, null, null, null );
                    if ( result.response == 'done' ) 
                    {
                        Ins.BuildModal( result.message, null, options.finalUrl, 10000 );   
                    } 
                    else 
                    {
                        ajaxInstall( baseUrl + '&step=' + result.response, baseUrl );
                    }
                }
            },
            error: function( xhr, textStatus, errorThrown )
            {
                Ins.BuildModal( 'Error', null, options.returnUrl + '&completed=installfailed', 10000 );
            }
        });
    };

    ajaxInstall( options.url, options.baseUrl );
});
