module.exports = {
  data: function () {
    return {
      comment: {
        text: ''
      },
      messages: []
    }
  },

  methods: {
    createComment: function (e) {
      e.preventDefault()
      var that = this
      client({path: 'comments', entity: this.comment}).then(
        function (response, status) {
          that.comment.text = ''
          that.messages = [ {type: 'success', message: 'Woof woof! Your dog was created'} ]
          Vue.nextTick(function () {
            document.getElementById('nameInput').focus()
          })
        },
        function (response, status) {
          that.messages = []
          for (var key in response.entity) {
            that.messages.push({type: 'danger', message: response.entity[key]})
          }
        }
      )
    }
  }
}
