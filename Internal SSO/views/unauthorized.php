<?php

http_response_code(403);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Prozel SSO &mdash; Unauthorized</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="/resources/images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/vendor/bootstrap/css/bootstrap.dark.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/fonts/iconic/css/material-design-iconic-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/vendor/animate/dist/animate.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="/resources/vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/vendor/select2/select2.min.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="/resources/vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/resources/css/util.css">
	<link rel="stylesheet" type="text/css" href="/resources/css/dist/main.min.css">
<!--===============================================================================================-->
</head>
<body>
	
	<div class="limiter">
		<div class="container-login100 animate__animated animate__flip">
			<div class="wrap-login100">
				<form class="login100-form validate-form">
					<span class="login100-form-title p-b-26">
						Prozel SSO
					</span>
					
					<div class="container-login100-form-messages">
						<div id="error_message" class="error_message" style="display: block;"><b>Error:</b> You do not have access to this resource!</div>

						<div class="details">
								<?php
									if(isset($_SESSION['uid'])){

										$db = new Database();
										$q = $db->db->prepare("SELECT `ID`, `sid`, `exp`, `user` FROM `access` WHERE `user` = :u_id AND `sid` = :r_sid");
										$q->execute([
											"u_id" => $_SESSION['uid'],
											"r_sid" => "domain:" . $_SERVER['HTTP_X_FORWARDED_HOST']
										]);

										$previous_access = $q->fetchAll();

										echo "<p>User: " . $_SESSION['username'];
										echo "<br>IP: " . $_SERVER['HTTP_X_REAL_IP'];
										echo "<br>Domain: " . htmlspecialchars($_SERVER['HTTP_X_FORWARDED_HOST']);
										echo "<br>URI: " . htmlspecialchars($_SERVER['REQUEST_URI']);
										echo "</p><hr><p>";
										foreach($previous_access as $access){
											echo "<br>Expired Access:";
											echo "<br>SID: " . $access['sid'];
											echo "<br>Expired at: " . date("h:i a d/m/Y", strtotime($access['exp']));
											echo "<br>";
										}
										echo "</p>";

									}
								?>
								<div class="footer">
								<a href="REDACTED">Services Gateway</a> | <a href="REDACTED">Logout</a>
								</div>
						</div>
						
					</div>
				</form>
			</div>
		</div>
	</div>
	

	<div id="dropDownSelect1"></div>
	
<!--===============================================================================================-->
	<script src="/resources/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="/resources/vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
	<script src="/resources/vendor/bootstrap/js/popper.js"></script>
	<script src="/resources/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="/resources/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="/resources/vendor/daterangepicker/moment.min.js"></script>
	<script src="/resources/vendor/daterangepicker/daterangepicker.js"></script>
<!--===============================================================================================-->
	<script src="/resources/vendor/countdowntime/countdowntime.js"></script>

</body>
</html>