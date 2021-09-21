$("#up_pwd_btn").on("click", function(e){
    e.preventDefault();
    $(".error_message").remove();
    $(".success_message").remove();
    $(".notice_message").remove();
    $(".disclaimer").prepend("<div class=\"notice_message animate__animated animate__rubberBand\"><strong>Processing: </strong> Please wait while we process your request.</div>");
    if($("#up_pwd_nw").val() == $("#up_pwd_cf").val()){
        $.ajax({
            url: "update_pwd",
            type: "post",
            data: {
                cu_pwd: $("#up_pwd_cu").val(),
                nw_pwd: $("#up_pwd_nw").val()
            },
            success: function(resp){
                if(resp.status == "OK"){
                    $(".notice_message").remove();
                    $(".disclaimer").prepend("<div class=\"success_message animate__animated animate__rubberBand\"><strong>Success: </strong> Your password has been updated.</div>");
                    $("#up_pwd_cu").val("");
                    $("#up_pwd_cf").val("");
                    $("#up_pwd_nw").val("");
                } else if (resp.error == "oldpwd"){
                    $(".notice_message").remove();
                    $(".disclaimer").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Your current password is incorrect.</div>");
                } else {
                    $(".notice_message").remove();
                    $(".disclaimer").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> An error occurred.</div>");
                }
            },
            error: function(resp){
                $(".notice_message").remove();
                $(".disclaimer").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> There was an error processing your request.</div>");
            }
        });
    }
    else {
        $(".notice_message").remove();
        $(".disclaimer").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Your passwords do not match.</div>");
    }

});
$("#logout_btn").on("click", function(e){
    e.preventDefault();
    window.location = "logout";
}); 
setInterval(loadOnPremesis, 1000);
function loadOnPremesis(){
    $.ajax({
        url: "updateTable",
        type: "post",
        data: {
            time_frame_1: $("#after_date").val(),
            time_frame_2: $("#before_date").val()
        },
        success: function(response) {
            if(response.status == "OK"){
                if($(".people_on_premesis table tbody").html() != response.data){
                    $(".people_on_premesis table tbody").html(response.data);
                }
            } else if (response.error == "auth"){
                window.location = "login";
            }
            else {
                alert("An error occured. Please refresh the page and try again.");
            }
        },
        error: function(data) {
            alert("An error occured. Please refresh the page and try again.");
        }
    });
}

$(".export_qld_health").on("click", function(e){
    e.preventDefault();
    window.open("download_emergency?dateAfter=" + $("#after_date").val() + "&dateBefore=" + $("#before_date").val(), '_blank');
});
$(".export_marketing").on("click", function(e){
    e.preventDefault();
    window.open("download_marketing", '_blank');
});

$(".people_on_premesis").on("click", ".mark_as_exited_btn", function(e){
    $.ajax({
        url: "mark_exit",
        type: "post",
        data: {
            ref_no: $(e.target).attr("data-refno")
        },
        success: function(resp) {
            if(resp.status == "OK"){
                loadOnPremesis();
            } else {
                alert("Failed to mark as exited.");
            }
        },
        error: function (resp){
            alert("Failed to mark as exited.");
        }
    });
    
});