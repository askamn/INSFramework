jQuery(document).ready(function(){
    jQuery.noConflict();
    jQuery('#ajax-search').keypress(function(e){
        if(e.which == 13)
        {
            jQuery('#ajax-submit').click();
        }
    });
    jQuery('#ajax-submit').on("click",function(e){
        e.preventDefault();
        Ins.loader( 'show' );
        jQuery("#result").html('');

        var data = { 
            templatename: jQuery('#ajax-search').val(),
            ajaxrequest: 'search', 
            crumb: options.crumb 
        };

        Ajax( options, data, Success );
    }); 

    jQuery(window).bind('keydown', function(event) {
        if (event.ctrlKey || event.metaKey) {
            switch (String.fromCharCode(event.which).toLowerCase()) 
            {
                case 's':
                    event.preventDefault();
                    jQuery('.ins-btn').click();
                break;
            }
        }
    });
    jQuery('.ins-btn').on("click",function(e){
        e.preventDefault();
        Ins.loader( 'show' );

        var data = { 
            templatedata: editor.getValue(),
            ajaxrequest: 'edit',
            crumb: options.crumb 
        };

        Ajax( options, data, Success_TemplateEdit );
    }); 
});

function Success_TemplateEdit( result )
{
    Ins.MsgBox( 'show', result.message );
    Ins.loader( 'hide' );
}

function Success( result )
{
    jQuery('#result').html(result.message);
    Ins.loader( 'hide' );
}