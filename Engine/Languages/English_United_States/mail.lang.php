<?php

$l['mail_general_error'] = "There was a problem sending you an email. Please try again later.";
$l['activation_subject'] = "Activate Your Account";
$l['activation_message'] = "
		<html>
		<head>
		<title>{1}</title>
		</head>
		<body>
		<p>Hello {2}</p>
		<br />
		<p>Thank you for registering on our site. This email contains your activation link. Please click it in order to activate your account.</p>
		<br />
		<p><a href=\"{3}/members.php?action=activate&code={4}\">Click here to visit {5} & activate your account.</a></p>
		<br/ >
		<p>If the above link does not work, please visit {5} & enter the below activation code when prompted.</p>
		<p>Code: {4}</p>
		<br />
		- {5}
		</body>
		</html>
";

$l['forgot_password_subject'] = "Forgot Password";
$l['forgot_password_message'] = "
		<html>
		<head>
		<title>{1}</title>
		</head>
		<body>
		<p>Hello {2},</p>
		<br />
		<p>You recently passed a \"Forgot my password\" request. Below is the code you need to input at the Forgot password page to get your new password.</p>
		<br />
		<p><a href=\"{3}/members.php?action=resetpassword&code={4}\">Click here to visit {5} & reset your password.</a></p>
		<br/ >
		<p>If the above link does not work, please visit {3}/index.php?action=resetpassword & enter the below code when prompted.</p>
		<p>Code: {4}</p>
		<br />
		- {5}
		</body>
		</html>
";		

?>