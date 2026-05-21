var efpic = efpic || {};

efpic.GalleryCollection = Backbone.Collection.extend({

    model: efpic.singleImage,
    url: '#',

    initialize: function() {

    },

    // Helper function to count selected images
    countSelected: function() {
        return this.where({selected: true}).length;
    }

});