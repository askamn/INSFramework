/** 
 * <pre>
 * Infusion Network Solutions
 * IN.Cms v0.5.0
 * Core JS
 * Last Updated: $Date: 2014-12-27 4:10:16 (Sun, 27 Dec 2014) $
 * </pre>
 * 
 * @author 		$Author: AskAmn$
 * @copyright	(c) 2014 Infusion Network Solutions
 * @license		http://www.infusionnetwork/licenses/license.php?view=main&version=2014
 * @package		IN.CMS
 * @since 		0.5.2; 22 August 2014
 */

var Ins = {
	/**
	 * Initializes
	 */
	init: function()
	{
		jQuery(document).ready(function(){
			jQuery( "body" ).append( '<div class="infusionLoaderContainer"><div class="infusionLoader"></div></div>' );
			jQuery( "body" ).append( '<div class="infusionMsgBoxContainer"><div class="infusionMsgBox"></div></div>' );

			Ins.checkBoxHandler();

			jQuery( '.infusionMsgBoxContainer' ).click( function(){  jQuery(this).fadeOut('slow') });
			jQuery( '.infusionMsgBoxContainer' ).delay( 5000 ).fadeOut( 400 );
		});
	}, 

	Loader: '<div class="ins-loader"><div class="ins-loader__inner"></div></div>',

	/**
	 * Handles checkboxes
	 */
	checkBoxHandler: function()
	{	
		/* Hide elements that are supposed to be visible when the checkbox is checked */
		jQuery( 'input[data-toggles-oncheckshow]' ).each( function(){
			var toggles = jQuery( this ).attr( 'data-toggles-oncheckshow' );
			/* Already checked? */
			if( !jQuery(this).is(':checked') )
			{
				jQuery( toggles ).hide();
			}
		} );

		jQuery( 'input[data-toggles-oncheckhide]' ).each( function(){
			var toggles = jQuery( this ).attr( 'data-toggles-oncheckhide' );
			/* Already checked? */
			if( jQuery(this).is(':checked') )
			{
				jQuery( toggles ).hide();
			}
		} );

		jQuery('input[type="checkbox"], input[type="radio"]').click(function(){
			var toggleShow = jQuery( this ).attr( 'data-toggles-oncheckshow' );
			var toggleHide = jQuery( this ).attr( 'data-toggles-oncheckhide' );

			/*if( typeof toggleShow !== typeof undefined && toggleShow !== false )
			{
				jQuery( toggleShow ).slideToggle();
			}
			if( typeof toggleHide !== typeof undefined && toggleHide !== false )
			{
				jQuery( toggleHide ).slideToggle();
			}*/

			if( jQuery(this).is(':checked') )
			{
				if( typeof toggleShow !== typeof undefined && toggleShow !== false )
				{
					jQuery( toggleHide ).prop('disabled', false).slideDown();
				}

				if( typeof toggleHide !== typeof undefined && toggleHide !== false )
				{
					jQuery( toggleHide ).prop('disabled', true).slideUp();
				}
			}
			else
			{
				if( typeof toggleShow !== typeof undefined && toggleShow !== false )
				{
					jQuery( toggleHide ).prop('disabled', true).slideUp();
				}

				if( typeof toggleHide !== typeof undefined && toggleHide !== false )
				{
					jQuery( toggleHide ).prop('disabled', false).slideDown();
				}
			}
		});
	},

	/**
	 * Handles our loader
	 */
	loader: function( request )
	{
		switch( request ) 
		{
			case 'show': 
				jQuery('.infusionLoaderContainer').fadeIn('slow');
        		jQuery('.infusionLoaderContainer').css('display', 'flex');
        		break;
        	case 'hide':
        		jQuery('.infusionLoaderContainer').fadeOut('slow');
        		break;
		}
	},

	/**
	 * Handles our generic loader
	 */
	GenericLoader: function( request )
	{
		jQuery( "body" ).append( '<div class="infusionGenericLoaderContainer"><div class="infusionGenericLoader"></div></div>' );
		switch( request ) 
		{
			case 'show': 
				jQuery('.infusionGenericLoaderContainer').fadeIn('slow');
        		jQuery('.infusionGenericLoaderContainer').css('display', 'flex');
        		break;
        	case 'hide':
        		jQuery('.infusionGenericLoaderContainer').fadeOut('slow');
        		break;
		}
	},

	/**
	 * Handles MsgBox
	 */
	MsgBox: function( request, message ) 
	{
		switch( request )
		{
			case 'show': 
				jQuery( '.infusionMsgBoxContainer' ).fadeIn( 'slow' ).delay( 5000 ).fadeOut( 400 );
				jQuery( '.infusionMsgBoxContainer' ).css( 'display', 'flex' );
				jQuery( '.infusionMsgBox' ).html( message ); 
				break;
			case 'hide':
				jQuery( '.infusionMsgBoxContainer' ).fadeOut( 'slow' );
				break;
		}
	},

	/**
	 * Decodes url
	 */
	UrlDecode: function( url ) 
	{
	 	return decodeURIComponent(url.replace(/\+/g, ' '));
	},

	/**
     * Builds a modal box
     */
    BuildModal: function( message, headerText, redirect, delay ) 
    {
		jQuery( "body" ).append( '<div class="infusionModalContainer"></div>' );
		
		if( headerText !== null )
		{
			jQuery( ".infusionModalContainer" ).html( '<div class="infusionModalHeader"></div><div class="infusionModal"></div>' );
			jQuery( ".infusionModalHeader" ).html( headerText );
			jQuery( ".infusionModal" ).html( message );
		}
		else
		{
			jQuery( ".infusionModalContainer" ).html( '<div class="infusionModal"></div>' );
			jQuery( ".infusionModal" ).html( message );
		}
		
		jQuery( '.infusionModalContainer' ).fadeIn( 'slow' );
		jQuery( '.infusionModalContainer' ).css( 'display', 'flex' );

		if( redirect !== null ) 
		{
			setTimeout(function(){ window.location = redirect; }, delay);
		}
    },

    /**
     * Our Ajaxifier
     */
    Ajaxify: function( form, returntype )
    {
    	var fselector = 'form[name='+form+']';
    	jQuery( fselector ).find( ':submit' ).click(function(e){
    		var height = jQuery( fselector ).height();
			jQuery( Ins.Loader ).insertAfter( fselector );
			jQuery( fselector ).hide();

			var marginTop = ( ( height/2 ) - 25 );  

			jQuery( '.ins-loader' ).css( { 'height' : height } );
			jQuery( '.ins-loader__inner' ).css( { 'margin-top' : marginTop } );

    		var data = jQuery(fselector).serialize();
    		var Options = {
    			url: jQuery(fselector).attr('action') + '&request=ajax',
    			type: jQuery(fselector).attr('method'),
    			dataType: returntype || 'json'
    		};
    		Ins.Ajax( Options, data, function(result){
    			if( result.statuscode == 1 )
    			{
    				jQuery( fselector ).parent().parent().parent().html( result.message );
    			}
    			else
    			{
    				alert( 'Something went wrong.' );
    			}
    		});
    		e.preventDefault();
    	});	
    },

	/**
	 * Handles AJAX Calls 
	 */
	Ajax: function( Options, Parameters, Callback, Silent ) 
	{      
		var url 			= Options.url;
		var type 			= Options.type        || 'POST';
		var contentType     = Options.contentType || 'application/x-www-form-urlencoded; charset=UTF-8';
		var dataType 	 	= Options.dataType    || 'json';
		var errorText       = Options.errorText   || 'Whoops! The server could not connected.';
		var silent 			= Silent 			  || true;

		for( var key in Parameters )
		{
			if( !Parameters[key] )
			{
				Parameters[key] = "NULL";
			}
		}
		
		jQuery.ajax({
		    url: url,
		    type: type,
		    data: Parameters,
		    //contentType: contentType, // What we are sending?
		    dataType: dataType,		  // What do we expect?	
		    success: Callback,
		    error: function(xhr, textStatus, errorThrown) 
		    {
		        jQuery('.error span').html('Failed!');
		        jQuery('.error').slideDown('slow');

		        if( Silent !== true )
		        {
		        	alert("ERROR:" + xhr.responseText+" - "+errorThrown);
		     	}
		    }

		});
	}
};

Ins.init();