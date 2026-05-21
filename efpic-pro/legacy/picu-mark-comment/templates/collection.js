var efpic = efpic || {};

efpic.MarkerCollection = Backbone.Collection.extend({

	model: efpic.singleMarker,
	url: '#',
	// localStorage: new Store( 'gallery' )

	initialize: function() {
		//console.log(this);
	},

	// Helper function to count selected images
	// countSelected: function() {
	// 	return this.where({selected: true}).length;
	// }

});