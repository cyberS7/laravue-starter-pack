module.exports = {

  data: function () {
    return {
      comments: [],
      messages: []
    }
  },

  methods: {
    // Let's fetch some comments
    fetch: function (successHandler) {
      var that = this
      client({ path: '/comments' }).then(
        function (response) {
          // Look ma! Puppies!
          that.$set('comments', response.entity.data)
          successHandler(response.entity.data)
        },
        function (response, status) {
          if (_.contains([401, 500], status)) {
            that.$dispatch('userHasLoggedOut')
          }
        }
      )
    },

    deleteDog: function (index) {
      var that = this
      client({ path: '/comments/' + this.comments[index].id, method: 'DELETE' }).then(
        function (response) {
          that.comments.splice(index, 1)
          that.messages = [{type: 'success', message: 'Great, dog purged.'}]
        },
        function (response) {
          that.messages.push({type: 'danger', message: 'There was a problem removing the dog'})
        }
      )
    }

  },

  route: {
    // Ooh, ooh, are there any new puppies yet?
    data: function (transition) {
      this.fetch(function (data) {
        transition.next({comments: data})
      })
    }
  }

}
