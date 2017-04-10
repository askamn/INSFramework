/**
 * Core: Search
 */

jQuery(document).ready(
	function(){
		//var timerObj, timerTime = 1000;
		var insModernSearch = jQuery( '#ins-fullscreensearch' ), 
			input = jQuery( '.ins-input' ), 
			ctrlClose = jQuery( '.ins-fullscreensearch-close' ),
			searchTerm, isOpen = isAnimating = false,

			toggleSearch = function(evt) 
			{
				// return if open and the input gets focused
				if( evt.type.toLowerCase() === 'focus' && isOpen ) 
					return false;

				var offsets = document.getElementById( 'ins-fullscreensearch' ).getBoundingClientRect();
				if( isOpen ) 
				{
					insModernSearch.removeClass('open');
					if( input.val() !== '' ) 
					{
						setTimeout(function() 
						{
							insModernSearch.addClass('hideInput');
							setTimeout(function() 
							{
								insModernSearch.removeClass('hideInput');
								input.val('');
							}, 300 );
						}, 500);
					}
					
					input.blur();
				}
				else 
					insModernSearch.addClass('open');
				isOpen = !isOpen;
			};

		input.focus( toggleSearch );
		ctrlClose.click( toggleSearch );

		/* Escape Key */
		document.addEventListener( 'keydown', function( ev ) 
		{
			var keyCode = ev.keyCode || ev.which;
			if( keyCode === 27 && isOpen ) {
				toggleSearch(ev);
			}
		} );

		jQuery( '.ins-submit' ).click( function(e){
			e.preventDefault(); 
			jQuery('.search-messagebar').html('<div class="search-message">' + options.searchMessage + '</div>');
			ajaxSearch();
			//clearTimeout(timerObj);
			//timerObj = setTimeout( ajaxSearch, timerTime );
			return false; 
		});

		input.keypress(function(e){
	        if(e.which == 13)
	        {
	        	jQuery('.search-messagebar').html('<div class="search-message">' + options.searchMessage + '</div>');
	            ajaxSearch();
	        }
	    });
		/*input.keyup(function(event) 
		{
			if( searchTerm == input.val() )
				return;
			jQuery('.ins-searchcolumns').html('<div class="search-message">' + options.searchMessage + '</div>');
			clearTimeout(timerObj);
			timerObj = setTimeout( ajaxSearch, timerTime );
		});

		input.keydown(function(){
			clearTimeout(timerObj);
		});*/

		ajaxSearch = function()
		{
			jQuery('.ins-searchcolumns').html('');
			var areas = "";
			searchTerm = input.val();
			if( searchTerm == '' )
			{
				jQuery('.ins-searchcolumns').html('');
				jQuery('.search-messagebar').html('<div class="search-error">' + options.searchMessageEmpty + '</div>');
				return;
			}

			if( searchTerm == 'about ins' )
			{
				jQuery('.ins-searchcolumns').html('<h2>Fury</h2><div class="ins-search-item" style="flex: 0 0 100%"><div class="ins-search-media-object"><h2>Infusion Network Solutions</h2></div></div>');
				jQuery('.search-messagebar').html('<div class="search-message">' + options.searchEnterToSearch + '</div>');
				return;
			} 

			jQuery('.ins-checkbox-wrapper :checkbox').each(function(){
				if( jQuery(this).is(':checked') )
					areas += jQuery(this).val() + '|';
			});	
			jQuery.getJSON(
				options.searchURL,
				{
					term: searchTerm,
					request: "ajax",
					locations: areas,
					crumb: options.crumb,
				},
				function(result)
				{
					var _html = "<h2>People</h2>";
					if( result.statuscode == 0)
					{
						jQuery('.search-messagebar').html('<div class="search-error">' + result.message + '</div>');
					}
					else
					{
						jQuery.each(result.data, function(key, array)
						{
							_html += '<div class="ins-search-item">';
							_html += '<a href="' + insoptions.siteUrl + '/index.php?app=core&module=members&uid=' + array.uid + '" class="ins-search-media-object">';
							var i = 0;
							jQuery.each(array, function(_key, _val) 
							{
								if( _key == 'uid' )
									return;
								if(i)
								{
									_html += '<h3>' + _val + '</h3>';
									i = 0;
								}
								else
								{
									_html += '<img class="round" src="' + insoptions.uploadsDir + '/avatars/' + _val + '" />';
									i = 1;
								}
							});
							_html += '</a>';
							_html += '</div>';
						});

						jQuery('.ins-searchcolumns').html(_html).fadeIn('slow');
						jQuery('.search-messagebar').html('<div class="search-message">' + options.searchEnterToSearch + '</div>');
					}
				}
			);
		}	

		/* Register form */
	}
);