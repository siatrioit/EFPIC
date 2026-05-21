var efpic = efpic || {};

efpic.ApprovedView = Backbone.View.extend({

	tagName: 'div',
	className: 'efpic-modal',
	id: 'efpic-approved',

	template: _.template( jQuery( "#efpic-approved" ).html() ),

	initialize: function( options ) {
		this.title = options.title;
	},

	render: function() {
		var approvedTemplate = this.template({title: this.title});
		this.$el.html( approvedTemplate );
		return this;
	}

});