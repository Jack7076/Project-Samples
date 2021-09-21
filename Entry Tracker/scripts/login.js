var recaptcha = "6LdQKbwZAAAAANpc-ZCOawabpjwwEWbXWXEARK1f";
$("#login_btn").on("click", function(e){
    e.preventDefault();
    $(".error_message").remove();
    $(".success_message").remove();
    $(".notice_message").remove();
    $(".contact_details_form").prepend("<div class=\"notice_message animate__animated animate__rubberBand\"><strong>Processing: </strong> Please wait while we process your request.</div>");
    grecaptcha.ready(function(){
        grecaptcha.execute(recaptcha, {action: 'adminAuth'}).then(function(token){
            $.ajax({
                url: "authenticate",
                type: "post",
                data: {
                    token,
                    username: $("#username").val(),
                    password: $("#password").val()
                },
                success: function(data) {
                    $(".notice_message").remove();
                    if(data.status == "OK"){
                        $(".contact_details_form").prepend("<div class=\"success_message animate__animated animate__rubberBand\"><strong>Success: </strong> Logged in successfully. Redirecting...</div>");
                        setTimeout(function(){
                            window.location = "index";
                        }, 300);
                    } else {
                        $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> " + data.message + " </div>");
                    }
                },
                error: function(data) {
                    $(".notice_message").remove();
                    $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> An error occurred while processing your request, please try again. </div>");
                }
            });
        });
    });
});

$("#cancel_btn").on("click", function(e){
    e.preventDefault();
    window.location = "/";
});