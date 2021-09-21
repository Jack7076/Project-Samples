<?php
$auth_handler = new Authorization_Handler();
$user_sids = $auth_handler->get_authorizations();

$db = new Database();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Prozel SSO &mdash; Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="resources/images/icons/favicon.ico" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/bootstrap/css/bootstrap.dark.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/fonts/iconic/css/material-design-iconic-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/animate/dist/animate.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/animsition/css/animsition.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/vendor/daterangepicker/daterangepicker.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="resources/css/util.css">
    <link rel="stylesheet" type="text/css" href="resources/css/dist/main.min.css">
    <!--===============================================================================================-->
</head>

<body style="overflow-y: scroll;">

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-dashboard animate__animated animate__fadeIn">
                <span class="dashboard-title p-b-26">
                    Prozel SSO
                </span>

                <div class="details">

                    <div class="current_user">
                        <p>UID: <?php echo $_SESSION['uid']; ?></p>
                        <p>Username: <?php echo $_SESSION['username']; ?></p>
                        <hr>
                        <p>Change Password: </p><br>

                        <div class="alert_space_change_pwd">
                            
                        </div>

                        <div class="password-update-container">
                            <div class="wrap-input100 validate-input cpw" data-validate="Enter Password">
                                <span class="btn-show-pass">
                                    <i class="zmdi zmdi-eye"></i>
                                </span>
                                <input class="input100" type="password" name="pass" id="inp_pass">
                                <span class="focus-input100" data-placeholder="Current Password"></span>
                            </div>
                            <div class="wrap-input100 validate-input npw" data-validate="Enter Password">
                                <span class="btn-show-pass">
                                    <i class="zmdi zmdi-eye"></i>
                                </span>
                                <input class="input100" type="password" name="pass" id="inp_new_pass">
                                <span class="focus-input100" data-placeholder="New Password"></span>
                            </div>
                            <div class="wrap-input100 validate-input cpw" data-validate="Enter Password">
                                <span class="btn-show-pass">
                                    <i class="zmdi zmdi-eye"></i>
                                </span>
                                <input class="input100" type="password" name="pass" id="inp_conf_pass">
                                <span class="focus-input100" data-placeholder="Confirm Password"></span>
                            </div>

                            <div class="container-login100-form-btn">
                                <div class="wrap-dash100-form-btn">
                                    <div class="login100-form-bgbtn"></div>
                                    <button class="login100-form-btn" id="change_pwd_btn">
                                        Change Password
                                    </button>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <p>SIDs:</p>
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>USID</th>
                                    <th>SID</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($user_sids as $sid) {
                                        echo "<tr>";
                                        echo "<td>";
                                        echo $sid['usid'];
                                        echo "</td>";
                                        echo "<td>";
                                        echo $sid['sid'];
                                        echo "</td>";
                                        echo "<td>";
                                        echo date("h:i a d/m/Y", strtotime($sid['exp']));
                                        echo "</td>";
                                        echo "</tr>";
                                    }

                                ?>
                            </tbody>
                        </table>

                        <?php
                            if($auth_handler->check_authorization("application:administrator")){
                                ?>
                                <hr>
                                Administration Section <br>

                                <table class="table table-striped table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                            $q = $db->db->prepare("SELECT `ID` as `uid`, `username`, `email` FROM `users`");
                                            $q->execute();

                                            $users = $q->fetchAll(PDO::FETCH_ASSOC);

                                            foreach($users as $user){
                                                echo "<tr>";
                                                echo "<td>";
                                                echo $user['uid'];
                                                echo "</td>";
                                                echo "<td>";
                                                echo $user['username'];
                                                echo "</td>";
                                                echo "<td>";
                                                echo $user['email'];
                                                echo "</td>";
                                                echo "<td>";
                                                echo "<a href=\"#\" class=\"edit_user_btn\" data-target=\"#usercontrol_modal\" data-toggle=\"modal\" data-uid=\"" . $user['uid'] . "\">Edit</a>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }

                                        ?>
                                    </tbody>
                                </table>

                                <?php
                            }
                        ?>

                    </div>

                    <div class="footer">
                        <a href="REDACTED">Return to Services Gateway</a> | <a href="REDACTED">Logout</a>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="usercontrol_modal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Manage User</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="wrap-input100 validate-input username">
                                <input class="input100 has-val" type="text" name="username_set" id="admin_set_username">
                                <span class="focus-input100" data-placeholder="Username"></span>
                            </div>
                            <div class="wrap-input100 validate-input email">
                                <input class="input100 has-val" type="text" name="email_set" id="admin_set_email">
                                <span class="focus-input100" data-placeholder="Email"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary">Save changes</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                        </div>
                    </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div id="dropDownSelect1"></div>

    <!--===============================================================================================-->
    <script src="resources/vendor/jquery/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="resources/vendor/animsition/js/animsition.min.js"></script>
    <!--===============================================================================================-->
    <script src="resources/vendor/bootstrap/js/popper.js"></script>
    <script src="resources/vendor/bootstrap/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="resources/vendor/select2/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="resources/vendor/daterangepicker/moment.min.js"></script>
    <script src="resources/vendor/daterangepicker/daterangepicker.js"></script>
    <!--===============================================================================================-->
    <script src="resources/vendor/countdowntime/countdowntime.js"></script>
    <script src="resources/js/dash.js"></script>
</body>

</html>