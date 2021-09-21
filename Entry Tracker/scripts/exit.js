var recaptcha = "6LdQKbwZAAAAANpc-ZCOawabpjwwEWbXWXEARK1f";
$("#complete_exit_btn").on("click", function(e){
    e.preventDefault();
    var ref_no = $("#ref_no").val();
    $(".success_message").remove();
    $(".error_message").remove();
    $(".notice_message").remove();
    $(".contact_details_form").prepend("<div class=\"notice_message animate__animated animate__rubberBand\"><strong>Processing: </strong> Please wait while we save your exit.</div>");
    grecaptcha.ready(function(){
        grecaptcha.execute(recaptcha, {action: 'exit'}).then(function(token){
            $.ajax({
                url: "/log_exit",
                type: "post",
                data: {
                    token,
                    ref_no
                },
                success: function(data) {
                    $(".notice_message").remove();
                    if(data.status == "OK"){
                        $(".contact_details_form").prepend("<div class=\"success_message animate__animated animate__rubberBand\"><strong>Success: </strong> Your exit has been logged, enjoy the rest of your day. Free beer tomorrow.</div>");
                        setTimeout(function(){
                            window.location = "/";
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