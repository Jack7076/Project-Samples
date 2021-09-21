var showPass = 0;
$(document).ready(() => {
    $("#change_pwd_btn").on("click", e => {
        e.preventDefault();

        if ($("#inp_new_pass").val().length < 7) {
            $(".alert_space_change_pwd").html("<div class=\"error_message animate__animated animate__flash\" style=\"display: block;\"><strong>Hold the fuck up! </strong> What the fuck do you think you are doing?! Make a half fucking decent password you dumbass.</div>");
            return;
        }

        if ($("#inp_new_pass").val() === $("#inp_conf_pass").val()) {
            $(".alert_space_change_pwd").html("<div class=\"alert alert-info animate__animated animate__flash\" style=\"display: block;\"><strong>Processing: </strong> Please wait while your request is being processed.</div>");
            $.ajax({
                url: "/update_password",
                type: "POST",
                data: {
                    cp: $("#inp_pass").val(),
                    np: $("#inp_new_pass").val()
                },
                success: data => {
                    if (data.error == false) {
                        $("#inp_pass").val("");
                        $("#inp_new_pass").val("");
                        $("#inp_conf_pass").val("");
                        $(".alert_space_change_pwd").html("<div class=\"alert alert-success animate__animated animate__fadeIn\" style=\"display: block;\"><strong>Success: </strong> Your password has been updated.</div>");
                        setTimeout(() => {
                            animate(".alert_space_change_pwd.alert-success", "fadeOut").then(() => {
                                $(".alert_space_change_pwd.alert-success").remove();
                            });
                        }, 2000);
                    } else {
                        console.error(data.error);
                        $(".alert_space_change_pwd").html("<div class=\"error_message animate__animated animate__flash\" style=\"display: block;\"><strong>Error: </strong> " + data.error + ".</div>");
                    }
                },
                error: () => {
                    $(".alert_space_change_pwd").html("<div class=\"error_message animate__animated animate__flash\" style=\"display: block;\"><strong>Error: </strong> It's a you problem; I can't be fucked to fix it.</div>");
                }
            })
        } else {
            $(".alert_space_change_pwd").html("<div class=\"error_message animate__animated animate__flash\" style=\"display: block;\"><strong>Error: </strong> Passwords do not match!</div>");
        }
    });

    /*==================================================================
    [ Show pass ]*/
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
    $('.input100').each(function() {
        $(this).on('blur', function() {
            if ($(this).val().trim() != "") {
                $(this).addClass('has-val');
            } else {
                $(this).removeClass('has-val');
            }
        })
    });

    $("#usercontrol_modal").on("show.bs.modal", (e) => {
        var modal = $(this);
        var sender = $(e.relatedTarget);

        $.ajax({
            url: "/api/get_user",
            type: "POST",
            async: false,
            data: {
                uid: $(sender).data("uid")
            },
            success: data => {
                if (data.error == false) {
                    $('#admin_set_username').val(data.user.username);
                    $('#admin_set_email').val(data.user.email);
                } else {
                    console.error(data.error);
                    alert(data.error);
                }
            }
        });

    });
});

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

$(".wrap-dashboard").one("animationend", () => {
    $(".wrap-dashboard").removeClass("animate__animated animate__fadeIn");
});