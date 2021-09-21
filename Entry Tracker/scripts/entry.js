var recaptcha = "6LdQKbwZAAAAANpc-ZCOawabpjwwEWbXWXEARK1f";
var animationEnd = "webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend";
var geocoder = new google.maps.Geocoder();
var searcherTimeout = null;
var geocodedLocations = {};
var telInput = null;
var children = 0;
$(document).ready(function(){
    // Intl Tel Input
    telInput = window.intlTelInput(document.getElementById("phone_number"), {
        initialCountry: "au",
        preferredCountries: [ "au", "nz", "us", "ca", "uk", "sg" ],
        customContainer: "phoneInput",
        utilsScript: "/lib/intl-tel-input/js/utils.js"
    });
    // Email or Address switcher
    $("#provide_address").on("click", function(e){
        // Prevent Redirect
        e.preventDefault();
        // Fade out Email entry box        
        $(this).closest(".row").addClass("animate__animated animate__bounceOut");
        $(this).closest(".row").one(animationEnd, function(ev){
            $(this).closest(".row").hide();
            $(this).closest(".row").removeClass("animate__bounceOut");
            // Fade in Address entry box
            $(".physical_address_wrapper").show();
            $(".physical_address_wrapper").addClass("animate__animated animate__bounceIn");
            $(".physical_address_wrapper").one(animationEnd, function(ev){
                $(".physical_address_wrapper").removeClass("animate__bounceIn");
            });
        });
    });
    $("#provide_email").on("click", function(e){
        // Prevent Redirect
        e.preventDefault();
        // Fade out Address entry box
        $(".address_selection_wrapper").hide();    
        $(".address_selection").html(""); 
        $(this).closest(".row").addClass("animate__animated animate__bounceOut");
        $(this).closest(".row").one(animationEnd, function(ev){
            $(this).closest(".row").hide();
            $(this).closest(".row").removeClass("animate__bounceOut");
            // Fade in Email Address entry box
            $(".email_address_wrapper").show();
            $(".email_address_wrapper").addClass("animate__animated animate__bounceIn");
            $(".email_address_wrapper").one(animationEnd, function(ev){
                $(".email_address_wrapper").removeClass("animate__bounceIn");
            });
        });
    });
    $("#physical_address").on("keyup keydown blur focus", function(e){
        // Check if Disabled
        if($("#physical_address").prop("disabled") == true){
            return;
        }
        // Animate Address Selector Entry
        if($(".address_selection_wrapper").css("display") == "none"){
            $(".address_selection_wrapper").show();
            $(".address_selection_wrapper").addClass("animate__animated animate__bounceIn");
            $(".address_selection_wrapper").one(animationEnd, function(ev){
                $(".address_selection_wrapper").removeClass("animate__bounceIn");
            });
        }
        if($(this).val() == ""){
            $(".address_selection").html("Start Typing an address ...");
            return;
        }
        clearTimeout(searcherTimeout);
        searcherTimeout = setTimeout(addressSearcher, 500);
    });
    $(".physical_address_wrapper").on("click", ".fix_address", function(e){
        e.preventDefault();
        $("#physical_address").prop("disabled", false);
        $("#physical_address").attr("data-location", "");
        $("#physical_address").attr("data-complete", "false");
        $(".fix_address").remove();
    });
    $(".address_selection").on("click", ".selectable_address", function(e){
        e.preventDefault();
        $("#physical_address").prop("disabled", true);
        $("#physical_address").attr("data-location", $(this).attr("data-locationid"));
        $("#physical_address").val($(this).text());
        $("#physical_address").attr("data-complete", "true");
        $("#provide_email").after("<a href=\"#\" class=\"fix_address\">Change Address</a>")
        $(".address_selection").html("");
    });
    // Marketing Checkbox Handler
    $(".checkbox_coms_txt").on("click", function(e){
        if($(e.target).prop("id") == "chk_marketing"){
            return;
        }
        e.preventDefault();
        document.getElementById('chk_marketing').checked = !document.getElementById('chk_marketing').checked;
    });
});

// Detail Filler
$("#fill_previous").on("click", function(e){
    e.preventDefault();
    $("#first_name").val($("#fill_previous").attr("data-fname"));
    $("#last_name").val($("#fill_previous").attr("data-lname"));
    $("#phone_number").val($("#fill_previous").attr("data-phone"));
    $("#email_address").val($("#fill_previous").attr("data-emailaddr"));
});

// Submit Handler
$("#contact_details_form_element").on("submit", function(e){
    e.preventDefault();
    hideSubmit();

    // Remove Previous Notifications
    $(".error_message").remove();
    $(".notice_message").remove();
    $(".success_message").remove();
    $(".contact_details_form").prepend("<div class=\"notice_message animate__animated animate__rubberBand\"><strong>Proccessing: </strong> Please wait while your details are saved. </div>");
    // Validate Form
    var errorcount = 0;

    var postal_address = null;
    if($("#physical_address").attr("data-complete") == "true"){
        postal_address = geocodedLocations[$("#physical_address").attr("data-locationid")];
    }
    if($("#first_name").val().length < 2){
        $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Please enter a valid First Name.</div>");
        errorcount++;
    }
    if($("#last_name").val().length < 2){
        $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Please enter a valid Last Name.</div>");
        errorcount++;
    }
    if(!validateEmail($("#email_address").val())){
        if($("#physical_address").attr("data-complete") != "true"){
            errorcount++;
            $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Please Provide a valid Email Address or Physical Address.</div>");
        }
    }
    if(!telInput.isValidNumber()){
        $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> Please Provide a valid Phone Number.</div>");
        errorcount++;
    }
    // Verify Not Spam
    grecaptcha.ready(function(){
        grecaptcha.execute(recaptcha, {action: 'submit'}).then(function(token){
            if(errorcount > 0){
                showSubmit();
                return;
            }
            var postal_address = "NA";
            var postal_code = "NA";
            var postal_id = "NA";
            if($("#physical_address").attr("data-complete") == "true"){
                postal_id = $("#physical_address").attr("data-location");
                postal_address = geocodedLocations[$("#physical_address").attr("data-location")]["formatted_address"];
                geocodedLocations[$("#physical_address").attr("data-location")]["address_components"].forEach(item => {
                    item['types'].forEach(type => {
                        if(type == "postal_code"){
                            postal_code = item.long_name;
                        }
                    });
                });
            }
            // Save Details
            var children = {};

            $(".child_form").each(function(i){
                children[i] = {
                    "first_name": $(this).find(".chd_append_fn").val(),
                    "last_name": $(this).find(".chd_append_ln").val()
                }
            });

            $.ajax({
                url: "save_details",
                type: "post",
                data: {
                    form: {
                        first_name: $("#first_name").val(),
                        last_name: $("#last_name").val(),
                        phone_number: telInput.getNumber(),
                        email_address: $("#email_address").val(),
                    },
                    token,
                    postal_address,
                    postal_code,
                    postal_id,
                    marketing: $("#chk_marketing").prop('checked'),
                    children
                },
                success: function(response) {
                    if(response.status == "OK"){
                        $(".contact_details_form").prepend("<div class=\"success_message animate__animated animate__rubberBand\"><strong>Success: </strong> Your details have been saved. Please wait while we fetch your reference number. </div>");
                        setTimeout(function(){
                            window.location = "/complete";
                        }, 300);
                    } else {
                        $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> " + response.message + "</div>");
                    }
                    showSubmit();
                },
                error: function (data) {
                    $(".contact_details_form").prepend("<div class=\"error_message animate__animated animate__rubberBand\"><strong>Error: </strong> An error occured, Please try again.</div>");
                    showSubmit();
                }
            });
        });
    });
});

// Management redirect
$("#management_btn").on("click", function(e){
    e.preventDefault();
    window.location = "management/index";
});

function hideSubmit() {
    // Remove Submit Button
    $("#submit_btn").addClass("animate__animated animate__bounceOut");
    $("#submit_btn").one(animationEnd, function(ev){
        $("#submit_btn").hide();
        $("#submit_btn").removeClass("animate__bounceOut");
    });
    // Display Async Loader
    $(".async_loader_wrapper").show();
    $(".async_loader_wrapper").addClass("animate__animated animate__bounceIn");
    $(".async_loader_wrapper").one(animationEnd, function(ev){
        $(".async_loader_wrapper").removeClass("animate__bounceIn");
    });
}
function showSubmit() {
    // Hide Async Loader
    $(".async_loader_wrapper").addClass("animate__animated animate__bounceOut");
    $(".async_loader_wrapper").one(animationEnd, function(ev){
        $(".async_loader_wrapper").hide();
        $(".async_loader_wrapper").removeClass("animate__bounceOut");
    });
    // Show Submit Button
    $("#submit_btn").show();
    $("#submit_btn").addClass("animate__animated animate__bounceIn");
    $("#submit_btn").one(animationEnd, function(ev){
        $("#submit_btn").removeClass("animate__bounceIn");
    });
}
function addressSearcher(){
    // Check if Disabled
    if($("#physical_address").prop("disabled") == true){
        return;
    }
    geocoder.geocode({
        address: $("#physical_address").val(),
        componentRestrictions: {
            country: 'AU'
        }
    },
        function(results, status){
            if(status == google.maps.GeocoderStatus.OK){
                $(".address_selection").html("");
                results.forEach(location => {
                    var validLocation = false;
                    location.types.forEach(type => {
                        if(type == "premise"){
                            validLocation = true;
                        }
                    });
                    if(validLocation){
                        $(".address_selection").append("<a href=\"#\" class=\"selectable_address\" data-locationid=\"" + location.place_id + "\">" + location.formatted_address + "</a>");
                        geocodedLocations[location.place_id] = location;
                    }
                });
            }
            // $(".address_selection").append("<a href=\"#\" class=\"selectable_address\" data-locationid=\"manual\">My Address isn't listed</a>");
        });
}
function validateEmail(email) {
    const re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    return re.test(String(email).toLowerCase());
}

$("#addChild").on("click", function(e){ e.preventDefault(); addChild() });

$("body").on("click", ".remove_child_entry", function(e){
    e.preventDefault();
    $("#child_form_" + $(e.currentTarget).attr("data-current")).remove();
});

function addChild(){
    children++;
    $("#child_details_entry").append("<div class=\"child_form\" id=\"child_form_" + children + "\"><label>Child's First Name:</label><input type=\"text\" class=\"chd_append_fn\" placeholder=\"Child's First Name\"><label>Child's Last Name:</label><input type=\"text\" class=\"chd_append_ln\" placeholder=\"Child's Last Name\"><a href=\"#\" data-current=\"" + children + "\" class=\"remove_child_entry\">Remove Above Child Entry Details</a></div>");
}