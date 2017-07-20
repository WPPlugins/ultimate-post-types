(function($){

	$(function() {
		$( '#post-type-settings.postbox' ).each(function(){
			var $box      = $( this ),
				$plural   = $( '#upt_pt_name' ),
				$singular = $( '#upt_pt_singular_name' ),
				labels = {
					singular: $singular.val(),
					plural: $plural.val()
				}

			$plural.on(   'change', function(){ labels.plural   = $plural.val(); });
			$singular.on( 'change', function(){ labels.singular = $singular.val(); });

			$( '#upt_pt_fine_tune' ).change(function(){
				if( ! $( this ).is( ':checked' ) || ! labels.singular || ! labels.plural )
					return;

				var defaults = {
					'upt_pt_add_new':      'Add %singular%',
					'upt_pt_add_new_item': 'Add New %singular%',
					'upt_pt_edit_item':    'Edit %singular%',
					'upt_pt_new_item':     'New %singular%',
					'upt_pt_view_item':    'View %singular%',
					'upt_pt_search_items': 'Search %plural%',
					'upt_pt_not_found':    'No %plural% found',
					'upt_pt_not_found_in_trash': 'No %plural% found in Trash',
					'upt_pt_parent_item_colon': 'Parent %singular%'
				};

				$.each( defaults, function( key, label ){
					if( ! $box.find( '#' + key ).val() ) {
						$box.find( '#' + key ).val( label.replace( '%singular%', labels.singular ).replace( '%plural%', labels.plural ) );
					}
				});
			});

			// Prevent WordPress's messages when saving
			$( window ).load(function(){
				$( window ).off( 'beforeunload.edit-post' );
			});
		});

		$( '#taxonmy-settings.postbox' ).each(function(){
			var $box      = $( this ),
				$plural   = $( '#upt_pt_name' ),
				$singular = $( '#upt_pt_singular_name' ),
				labels = {
					singular: $singular.val(),
					plural: $plural.val()
				}

			$plural.on(   'change', function(){ labels.plural   = $plural.val(); });
			$singular.on( 'change', function(){ labels.singular = $singular.val(); });

			$( '#upt_pt_fine_tune' ).change(function(){
				if( ! $( this ).is( ':checked' ) || ! labels.singular || ! labels.plural )
					return;

				var defaults = {
					'upt_pt_add_new':      'Add %singular%',
					'upt_pt_add_new_item': 'Add New %singular%',
					'upt_pt_edit_item':    'Edit %singular%',
					'upt_pt_new_item':     'New %singular%',
					'upt_pt_view_item':    'View %singular%',
					'upt_pt_search_items': 'Search %plural%',
					'upt_pt_not_found':    'No %plural% found',
					'upt_pt_not_found_in_trash': 'No %plural% found in Trash',
					'upt_pt_parent_item_colon': 'Parent %singular%'
				};

				$.each( defaults, function( key, label ){
					if( ! $box.find( '#' + key ).val() ) {
						$box.find( '#' + key ).val( label.replace( '%singular%', labels.singular ).replace( '%plural%', labels.plural ) );
					}
				});
			});

			// Prevent WordPress's messages when saving
			$( window ).load(function(){
				$( window ).off( 'beforeunload.edit-post' );
			});
		});
	});

	
	
})(jQuery);