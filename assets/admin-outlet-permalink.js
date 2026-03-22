( function () {
	'use strict';

	function onPropertyChange() {
		var cfg = window.pwOutletPermalink;
		if ( ! cfg || ! cfg.postId || ! cfg.restBase ) {
			return;
		}

		var propertySelect = document.getElementById( '_pw_property_id' );
		if ( ! propertySelect ) {
			return;
		}

		propertySelect.addEventListener( 'change', function () {
			var propertyId = this.value;
			var postId = cfg.postId;
			if ( ! postId ) {
				return;
			}

			wp.apiFetch( {
				path:
					'/wp/v2/' +
					encodeURIComponent( cfg.restBase ) +
					'/' +
					postId +
					'?context=edit&pw_property_id_preview=' +
					encodeURIComponent( propertyId ),
			} )
				.then( function ( post ) {
					if ( ! post ) {
						return;
					}

					if ( wp.data && wp.data.dispatch && wp.data.select ) {
						var edit = wp.data.dispatch( 'core/editor' );
						var patch = {};
						if ( post.link ) {
							patch.link = post.link;
						}
						if ( post.permalink_template ) {
							patch.permalink_template = post.permalink_template;
						}
						if ( Object.keys( patch ).length ) {
							edit.editPost( patch );
						}
					}

					var link = post.link;
					if ( ! link ) {
						return;
					}
					var permalinkAnchor = document.querySelector(
						'.editor-post-publish-panel__permalink a, .editor-post-permalink a, [class*="permalink"] a'
					);
					if ( permalinkAnchor ) {
						permalinkAnchor.textContent = link;
						permalinkAnchor.href = link;
					}
				} )
				.catch( function () {} );
		} );
	}

	wp.domReady( onPropertyChange );
} )();
