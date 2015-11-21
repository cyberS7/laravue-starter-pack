module.exports = {

  data: function () {
    return {
      messages: [],
      comments: {}
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
     createComment: function (e) {
      e.preventDefault()
        var that = this
        client({path: 'comments', entity: this.newComment}).then(
          function (response, status) {
        
            that.messages = [ {type: 'success', message: 'Woof woof! Your dog was created'} ]
            var data = that.newComment
            data.id = response.entity.id
            data.text = that.newComment.text
            that.comments.push(data)
            that.newComment = {text: '', id: ''}
            // that.comment.text = ''
            // Vue.nextTick(function () {
            //   document.getElementById('nameInput').focus()
            // })
          },
          function (response, status) {
            that.messages = []
            for (var key in response.entity) {
              that.messages.push({type: 'danger', message: response.entity[key]})
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
