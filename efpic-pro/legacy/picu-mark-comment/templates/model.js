var efpic = efpic || {};

efpic.singleMarker = Backbone.Model.extend({

	defaults: {
		id: '',
		time: '',
		user: '',
		comment: '',
		x: 0,
		y: 0
	}

});