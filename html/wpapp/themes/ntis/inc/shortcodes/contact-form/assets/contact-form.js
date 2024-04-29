(function( $ ) {
	'use strict';

    $(function() {
        const forms = document.querySelectorAll('.needs-validation');
		Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
            }, false)
                  
            form.onsubmit = function(e){
                if (form.checkValidity() !== false) {
                    var formData = new FormData(form);
                    var formAction = form.getAttribute('action');
                    var formMethod = form.getAttribute('method');

                    e.preventDefault();
                    $.ajax({
                        url: formAction,
                        type: formMethod,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if(data.success){
                                form.reset();
                                form.classList.remove('was-validated');
                                var successMessage = document.createElement('div');
                                successMessage.className = 'alert alert-success';
                                successMessage.innerHTML = data.message;
                                form.appendChild(successMessage);
                                setTimeout(function(){
                                    successMessage.remove();
                                }, 5000);
                            }else{
                                var errorMessage = document.createElement('div');
                                errorMessage.className = 'alert alert-danger';
                                errorMessage.innerHTML = data.message;
                                console.error(data.console);
                                form.appendChild(errorMessage);
                                setTimeout(function(){
                                    errorMessage.remove();
                                }, 5000);
                            }
                        },
                        error: function (data) {
                            console.error('Error: '+data);
                        }
                    });

                    return true;
                }else{
                    e.preventDefault();
                    e.stopPropagation();
                    return false; 
                }
            }
        });
    });
    

})( jQuery );