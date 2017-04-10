<?php

define('THIS_SCRIPT', 'members.php');
define('IN_INS', 1);
 
/* This is the main/core file that must be executed before anything. */
require_once "./init.php";
require_once INS_ROOT."/core/global.php";
		
/* These are the general variables available in almost every request. */
$action = $ins->input['action'];
$do = $ins->input['do'];

$not_logged_in = array("login", "register", "forgot", "resetpassword");
$logged_in = array();

/* Load global language phrases */
$lang->load("members");
/* Load necessary language phrases as per action */
if(in_array($action, $not_logged_in))
{
	$lang->load("mail");
}

/* Actions codded below are executed when user is logged out */

/* 1. User is not logged in. Neither is he on the login or the signup page. In this case, we show them this: */
if (!$user->INSsession() && $action != "login" && $action != "signup") 
{   
	$title = $lang->login_or_register_title." - ".$ins->settings['site']['name'];
    eval("\$members_login_or_register_registersection = \"".$templates->get("members_login_or_register_registersection")."\";");
    eval("\$members_login_or_register_loginsection = \"".$templates->get("members_login_or_register_loginsection")."\";");
    eval("\$members = \"".$templates->get("members_login_or_register")."\";");
}

/* 2. User is not logged in. But in here, he is either on 'login' page or 'register' page. */
if (!$user->INSsession())
{
    /* 2.1.  Login page: */
	if($action == "login")
	{
		if($do == "do_login")
		{
			$password = sanitize($ins->input['password']); 
			$username = sanitize($ins->input['username']); 
			
			if($user->validate_login($username, $password))
			{
			    if($user->activation_state == 1)
				{
					$user->create_session(); 
					redirect("{$ins->settings['site']['url']}/index.php");
				}
				else
				{
					redirect("{$ins->settings['site']['url']}/members.php?action=activate");
				}
			}
		}
		eval("\$members_login_or_register_loginsection = \"".$templates->get("members_login_or_register_loginsection")."\";");
		
		$title = $lang->login_title." - ".$ins->settings['site']['name'];
		eval("\$members = \"".$templates->get("members_login")."\";"); 
	}

	/* 2.1.  SignUp page: */
	elseif($action == "signup")
	{
		if($do == "do_register")
		{
			require_once ROOT."/classes/class_userhandler.php";
			$userhandler = new UserHandler;
			
			$errors = array();
			
			$userhandler->username = htmlentities($ins->input['username']);
		    $userhandler->email = htmlentities($ins->input['email']);
		    $userhandler->password = md5(sha1(sha1(sha1(md5($ins->input['password'])))));
		   
		    if($db->duplicates($userhandler->username, "username") === false)
			{ 
				$errors[] = "Username already in use.";	
			} 
		    if($db->duplicates($userhandler->email, "email") === false)
			{
			    $errors[] = "Email already in use.";
			}
			
			/* If, in any case our jQuery validator failed, then these come in handy */
			if($userhandler->username == "" || $userhandler->password == "" || $userhandler->email == "")
			{
				$errors[] = "You left some fields blank.";
			}
			if(strlen($userhandler->username) < 4 || strlen($userhandler->username) > 14)
			{
				$errors[] = "Username must be in the range of 4-14 chars.";
			}
			if(strlen($userhandler->password) < 8)
			{
				$errors[] = "Password must be atleast 8 characters";
			}
			if(!$userhandler->emailcheck())
			{
				$errors[] = "The email pattern is found to be invalid.";
			}
			
			/* No errors */
			if(empty($errors))
			{
				/* We have instant activation enabled */
				if($ins->settings['users']['instant_activation'] == 1)
				{
					$userhandler->register("ia"); 	
				}
				else
				{
					$userhandler->register(); 
					/* The below conditionals check if the Activation mail was sent or not. */
					if($userhandler->errors_code)
					{
						redirect($ins->settings['site']['url']."/members.php?action=activate&error_code={$userhandler->error_code}"); 	
					}
					else
					{
						redirect($ins->settings['site']['url']."/members.php?action=activate&ref_code=signup");
					}
				}	
			}
			else
			{	
				$formerrors = "";
				foreach($errors AS $error)
				{
					eval("\$formerrors .= \"".$templates->get("errors")."\";"); 
				}
			}
		}
		eval("\$members_login_or_register_registersection = \"".$templates->get("members_login_or_register_registersection")."\";");
		$title = $lang->register_title." - ".$ins->settings['site']['name'];
		eval("\$members = \"".$templates->get("members_register")."\";"); 
	}
	
	/* 2.3 Activation Page */
	elseif($action == "activate")
	{
		$title = $lang->activate_title." - ".$ins->settings['site']['name'];
		$code = $db->escape_string($ins->input['code']);
		$error_code = $db->escape_string($ins->input['error_code']);
		$ref_code = $db->escape_string($ins->input['ref_code']);
		$perform = $db->escape_string($ins->input['perform']);
		$style = "";
		
		/* 2.3.1.  User reached this page through URL, show them general Activation box */
		if($ref_code == "" && $error_code == "" && $code == "")
		{
			eval("\$members = \"".$templates->get("members_activate")."\";"); 
		}
		/* 2.3.2.  User reached this page through Signup. */
		if($ref_code == "signup" && $error_code == "" && $code == "")
		{
			$message = "Please verify your email by visiting your email & typing the activation code here, or visiting the link provided in your email.";
			eval("\$members = \"".$templates->get("members_activate")."\";"); 
		}
		/* 2.3.3.  User reached this page through Signup but the script wasn't able to send him email. */
		if($ref_code == "signup" && $error_code != "" && $code == "" || $ref_code == "" && $error_code != "" && $code == "")
		{	
			/* 2.3.3.1.  Mail not Sent. */
			if($error_code == 1)
			{		
				$message = "We were unable to send you an email. Please try again by clicking the link below.";
				$message .= "<br />";
				$message .= "<a id=\"form-continue\" class=\"big-btn cta-button\" href=\"{$ins->settings['site']['url']}/members.php?action=activate&perform=resend\">Resend Activation Email</a>";
				$style = "display:none;";
			}
			/* 2.3.3.2.  Wrong activation code. */
			elseif($error_code == 2)
			{
				$message = "The activation code you entered was wrong. Please try again.";
				$style = "";
			}
			/* 2.3.3.3.  Already activated. */
			elseif($error_code == 3)
			{
				$message = "You are already activated.";
				$style = "display:none";
			}
			eval("\$members = \"".$templates->get("members_activate")."\";"); 
		}
		/* 2.3.4.  So we got an activation code. Let's try to activate the user */
		if($code != "")
		{
			if($user->activate($code))
			{
				redirect("{$ins->settings['site']['url']}/members.php?action=login");
			}
			elseif($user->activate($code) == 3)
			{
				redirect("{$ins->settings['site']['url']}/members.php?action=login&error_code=3");
			}
			else
			{
				redirect("{$ins->settings['site']['url']}/members.php?action=activate&error_code=2");
			}
		}
	}
	
	/* 2.4. Forgot Password Page */
	elseif($action == "forgot")
	{
		$title = $lang->forgot_title;
		eval("\$members = \"".$templates->get("members_forgot")."\";");
	}	
	
	/* 2.5 Request New Password Page, STEP 2. */
	elseif($ins->input['action'] == "do_forgot_pass" && $ins->request_method == "post")
	{
		$title = $lang->forgot_title;
		$email = $ins->input['email'];
		$userdata = $user->get("email", $db->escape_string($ins->input['email']));
		if($userdata === false)
		{
			$lang->mail_not_found = $lang->parse($lang->mail_not_found, $email);
			error($lang->mail_not_found);
		}
		$row = $userdata;
		
		require_once ROOT."/classes/class_userhandler.php";
		$userhandler = new UserHandler;
		$code = $userhandler->generate_code();
		
		/* Delete any old requests */
		$db->smart_query("DELETE FROM `user_requests` WHERE `uid` = '{$row['uid']}' AND `rtype` = 'p'");
		/* Let us create a new request */ 
		$db->smart_query("INSERT INTO `user_requests`(`uid`,`code`,`rtype`,`misc`) VALUES('{$row['uid']}', '{$code}', 'p', '{$email}')");
		
		$lang->forgot_password_message = $lang->parse($lang->forgot_password_message, $lang->forgot_password_subject, $row['username'], $ins->settings['site']['url'], $userhandler->generate_code(), $ins->settings['site']['name']);
		
		mailer($email, $lang->forgot_password_subject, $lang->forgot_password_message, true);
		
		$message = $lang->forgot_mail_sent;
		eval("\$members = \"".$templates->get("members_forgot_done")."\";");
	}	
	
	/* 2.5. Reset Password Page */
	elseif($action == "resetpassword")	
	{
		if($ins->input['do'] == "do_reset_pass" && $ins->input['username'] && $ins->input['code'])
		{
			$userdata = $user->get("username", $ins->input['username']); 
			if($userdata === false)
			{
				$lang->resetpassword_wrong_username = $lang->parse($lang->resetpassword_wrong_username, $ins->input['username']);
				error($lang->resetpassword_wrong_username);
			}
			else
			{
				$row = $db->smart_query("SELECT * FROM `user_requests` WHERE `uid` = {$userdata['uid']}", array("rows"=>1));
				if($db->num_rows() == 0)
				{	
					error($lang->resetpassword_no_request_found);
				}
				if($row['code'] ==  $ins->input['code'])	
				{
					/* All went right. Reset the password & mail the user his new new password. */
					$password = random_str();
					$password = $user->encode($password);
					
					$db->smart_query("UPDATE `users` SET `password` = '{$password}' WHERE `uid` = {$row['uid']}");
					mailer($email, $lang->forgot_password_subject, $lang->forgot_password_message, true);
					
					$db->smart_query("DELETE FROM `user_requests` WHERE `uid` = {$row['uid']} AND `rtype` = 'p'");
					
					$message = $lang->resetpassword_done;
					$title = $lang->resetpassword_title_done;
					eval("\$members = \"".$templates->get("members_resetpassword_done")."\";");
				}
			}
		}
		else
		{
			$title = $lang->resetpassword_title;
			$code = $ins->input['code'];
			eval("\$members = \"".$templates->get("members_resetpassword")."\";");
		}
	}
}

/* The below actions are executed only if user is signed in. */

if($user->INSsession())
{
	/* Check if the admin wants us to redirect his users after login */
	if($ins->settings['users']['login_redirect'] != "")
	{
		redirect($ins->settings['users']['login_redirect']);
	}
	
    /* 1.1.  User wants to logout */
	if($action == "logout")
	{
		if(sanitize($ins->input['logged_out'] == ''))
		{
			$user->logout();	
			redirect($_url."/members.php?action=logout&logged_out=true") ;
		}
		elseif(sanitize($ins->input['logged_out'] == 'true'))
		{
			eval("\$members = \"".$templates->get("members_logout")."\";"); 	
		}
	}

	/* 1.2.  No action set? Show them to profile page. */
	elseif($action == "" || $action == 'profile')
	{
		if(isset($ins->input['uid']))
		{
			$ins->input['uid'] = intval($ins->input['uid']);
			$user->get("uid", $ins->input['uid']);
		}	
		else
		{
			$user->get("uid", $user->uid);
		}	
		eval("\$members = \"".$templates->get("members_profile")."\";"); 
	}
	
	elseif($action == 'editprofile')
	{	
		if($ins->request_method == "post" && isset($ins->input['submit']))
		{
			// Fields that can be empty
			$fields = array(
				'facebook' => $ins->input['facebook'],
				'twitter' => $ins->input['twitter'],
				'google-plus' => $ins->input['google-plus'],
			);
			
			// Fields that cannot be empty
			$array = array(
				'username' => $ins->input['username'],
				'email' => $ins->input['email'],
			);	
			
			foreach($fields AS $key => $value)
			{
				if($value != "")
					$array[$key] = $value;
			}
			
			// We only change the password if the field ain't blank
			if($ins->input['password'] != '')
			{
				$array['password'] = $ins->encode($ins->input['password']);
			}
			
			foreach($array AS $key => $value)
			{
				if($value == "")
				{
					$crash = true;
					$empty[] = "'{$key}'";
				}	
			} 
			
			if(!class_exists('Upload'))
			{
				require_once INS_ROOT."/core/classes/formhandler.class.php";
				require_once INS_ROOT."/core/classes/upload.class.php";
			}
			$upload = new Upload();
			$upload->setUploadField('file');
			$upload->maxFileSize = 1024*150; // 150 KB
			$upload->useRandomName = true;
			$upload->parse_dangerous_scripts = 1;
			$upload->allowedExtensions = array('gif', 'jpg', 'jpeg', 'png');
			$upload->uploadFileLocation = 'events'; 
			$upload->run();
			
			if($upload->errorString != '')
			{
				$crash = true;
			}
			
			if($crash === true)
			{
				if($upload->errorString != '')
				{
					$notify = "<div class=\"form-message error\">{$upload->errorString}</div>";
				}
				if(is_array($empty))
				{
					$empty = implode(', ', $empty);
					$notify .= "<div class=\"form-message error\">{$lang->admin_fields_empty} - {$empty}</div>";
				}
			}		
			else	
			{
				if($upload->uploadBox != false)
				{
					$user->removeAvatar($ins->input['uid']);
					$array['avatar'] = $upload->parsedFileName;
				}	
				$db->update("users", $array, "`uid`='{$ins->input['uid']}'");
			}	
		}
		
		$user->get("uid", $user->uid);
		eval("\$members = \"".$templates->get("members_profile_edit")."\";"); 
	}
} 

/* After we have done all the if-else, let us output our final parsed pure HTML content. */ 
$templates->output($members); 
?>