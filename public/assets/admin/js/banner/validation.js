var FormValidation = (function () {
  var handleValidation = function () {
    var form = $('#validateForm')

    form.validate({
      errorElement: 'span',
      errorClass: 'help-block help-block-error',
      focusInvalid: false,
      ignore: '',  
      rules: {
        bannerImage: {
            extension: "jpg|jpeg|png" // Adjust the allowed extensions
        }
      },
      messages: {
        bannerImage: {
          extension: "Please select a file with a valid extension (jpg, jpeg, png)"
        }
      },

      highlight: function (element) {
        $(element).closest('.form-group .form-control').addClass('is-invalid')
      },
      unhighlight: function (element) {
        $(element).closest('.form-group .form-control').removeClass('is-invalid').addClass('is-valid')
      },
      errorPlacement: function (error, element) {
        if (element.parent('.input-group').length || element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
          error.insertAfter(element.parent())
        } else {
          error.insertAfter(element)
        }
      },
      success: function (label) {
        label.closest('.form-group .form-control').removeClass('is-invalid')
      }
    })
  }

  return {
    init: function () {
      handleValidation()
    }
  }
})()

jQuery(document).ready(function () {
  FormValidation.init()
})
