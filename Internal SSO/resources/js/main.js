(function($) {
    "use strict";

    // Add Click Handler to Title
    $("login100-form-title").on("click", e => {
        e.preventDefault();
        window.location = "REDACTED";
    });

    /*==================================================================
    [ Focus input ]*/
    $('.input100').each(function() {
        $(this).on('blur', function() {
            if ($(this).val().trim() != "") {
                $(this).addClass('has-val');
            } else {
                $(this).removeClass('has-val');
            }
        })
    })


    /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit', function(e) {
        e.preventDefault();
        $("#processing_message").show();
        var check = true;

        for (var i = 0; i < input.length; i++) {
            if (validate(input[i]) == false) {
                showValidate(input[i]);
                check = false;
            }
        }

        if (check) {
            $.ajax({
                url: "/login",
                type: "POST",
                data: {
                    username: $("#inp_user").val(),
                    password: $("#inp_pass").val()
                },
                success: data => {
                    if (data.error == false) {
                        $("#processing_message").hide();
                        $("#success_message").html("<strong>Success: Redirecting ...</strong>");
                        $("#success_message").show();
                        animate('#success_message', 'tada');
                        $("#error_message").hide();
                        setTimeout(() => {
                            window.location = data.location;
                        }, 800)
                    } else {
                        $("#processing_message").hide();
                        $("#success_message").hide();
                        $("#error_message").show();
                        animate('#error_message', 'flash');
                        $("#error_message").html("<strong>Error: </strong> " + data.error);
                    }
                },
                error: data => {
                    $("#processing_message").hide();
                    $("#error_message").show();
                    animate('#error_message', 'flash');
                    $("#error_message").html("<strong>Error: </strong> You fucked it.");
                    console.error({ data });
                }
            });
        }

        return check;
    });


    $('.validate-form .input100').each(function() {
        $(this).focus(function() {
            hideValidate(this);
        });
    });

    function validate(input) {
        if ($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if ($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        } else {
            if ($(input).val().trim() == '') {
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }

    /*==================================================================
    [ Show pass ]*/
    var showPass = 0;
    $('.btn-show-pass').on('click', function() {
        if (showPass == 0) {
            $(this).next('input').attr('type', 'text');
            $(this).find('i').removeClass('zmdi-eye');
            $(this).find('i').addClass('zmdi-eye-off');
            showPass = 1;
        } else {
            $(this).next('input').attr('type', 'password');
            $(this).find('i').addClass('zmdi-eye');
            $(this).find('i').removeClass('zmdi-eye-off');
            showPass = 0;
        }

    });


})(jQuery);

const animate = (element, animation, prefix = 'animate__') =>
    // We create a Promise and return it
    new Promise((resolve, reject) => {
        const animationName = `${prefix}${animation}`;
        const node = document.querySelector(element);

        node.classList.add(`${prefix}animated`, animationName);

        // When the animation ends, we clean the classes and resolve the Promise
        function handleAnimationEnd(event) {
            event.stopPropagation();
            node.classList.remove(`${prefix}animated`, animationName);
            resolve('Animation ended');
        }

        node.addEventListener('animationend', handleAnimationEnd, { once: true });
    });