(function($) {

	var methods = { init :  function( options ) { 
    	var settings = $.extend({
			unwanted : '.post-info, .post-meta, .left-corner, .right-corner',
			header_class : 'accordion-header',
			content_class : 'accordion-content',
			header : 'h3',
			heightStyle: 'content',
			collapsible: true,
			active: false
	    	}, options);
	    
    	return this.each(function() { 
			$(this).find(settings.unwanted).remove(); //strip unwanted elements  					
			$(this).find(settings.header).removeClass().addClass(settings.header_class);
			$(this).find(settings.header).next().removeClass().addClass(settings.content_class);
			$(this).accordion(settings);	      
		});
    }       
  };

	$.fn.gcaccordion = function( method ) {
    	if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
      		return methods.init.apply( this, arguments );
    	} else {
      		$.error( 'Method ' +  method + ' does not exist on gcaccordion' );
    	}    
	};
	
})(jQuery);