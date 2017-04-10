/* Handles AJAX Calls */
function Ajax( Options, Parameters, Callback ) 
{      
	var url 			= Options.url;
	var type 			= Options.type        || 'POST';
	var contentType     = Options.contentType || 'application/x-www-form-urlencoded; charset=UTF-8';
	var dataType 	 	= Options.dataType    || 'json';
	var errorText       = Options.errorText   || 'Whoops! The server could not connected.';

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
	        alert("ERROR:" + xhr.responseText+" - "+errorThrown);
	    }

	});
}
