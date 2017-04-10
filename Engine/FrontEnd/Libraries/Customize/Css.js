jQuery(document).ready(function(){
    jQuery.noConflict();

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
            cssdata: editor.getValue(),
            crumb: options.crumb 
        };

        Ajax( options, data, Success );
    }); 
});

function Success( result )
{
    Ins.MsgBox( 'show', result.message );
    Ins.loader( 'hide' );
}

