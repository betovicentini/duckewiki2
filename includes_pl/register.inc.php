<?php
include_once 'db_connect.php';
include_once 'psl-config.php';

$error_msg = "";

if (isset($_POST['username'], $_POST['email'], $_POST['p'])) {
	// Sanitize and validate the data passed in
	$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
	$email = filter_var($email, FILTER_VALIDATE_EMAIL);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error_msg .= '<p class="error">Email inválido.</p>';
	}

	$pwd = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
	if (strlen($pwd) != 128) {
		// The hashed pwd should be 128 characters long.
		// If it's not, something really odd has happened
		$error_msg .= '<p class="error">Senha inválida.</p>';
	}

	// Username validity and password validity have been checked client side.
	// This should should be adequate as nobody gains any advantage from
	// breaking these rules.

	$q = "select id from usr where email = $1 limit 1";
	$res = pg_query_params($conn,$q,[$email]);

	// check existing email  
	if ($res) {
		if (pg_num_rows($res) == 1) {
			$error_msg .= '<p class="error">Email já cadastrado.</p>';
		}
	} else {
		$resErr = pg_last_error($conn);
		/*pg_send_query_params($conn,$q,[$email]);
		$res = pg_get_result($conn);
		$resErr = pg_result_error($res);*/
		$error_msg .= "<p class='error'>Database error Line 40: $resErr</p>";
	}

	// check existing username
	$q = "select id from usr where username = $1 limit 1";
	$res = pg_query_params($conn,$q,[$username]);
	if ($res) {
		if (pg_num_rows($res) == 1) {
			$error_msg .= '<p class="error">Nome de usuário já cadastrado.</p>';
		}
	} else {
		pg_send_query_params($conn,$q,[$email]);
		$res = pg_get_result($conn);
		$resErr = pg_result_error($res);
		$error_msg .= "<p class='error'>Database error Line 55: $resErr</p>";
	}

	// TODO: 
	// We'll also have to account for the situation where the user doesn't have
	// rights to do registration, by checking what type of user is attempting to
	// perform the operation.

	if (empty($error_msg)) {
		// Create a random salt
		$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));

		// Create salted password 
		$pwd = hash('sha512', $pwd.$random_salt);

		// Insert the new user into the database 
		$q = "insert into usr (username,email,pwd,salt) values ($1,$2,$3,$4);";
		$res = pg_query_params($conn,$q,[$username,$email,$pwd,$random_salt]);
		if ($res == false) {
			pg_send_query_params($conn,$q,[$username,$email,$pwd,$random_salt]);
			$res = pg_get_result($conn);
			$resErr = pg_result_error($res);
			header("Location: ../html/plantas/error.php?err=Registration failure: INSERT [$resErr]");
		} else {
			header('Location: ./register_success.php');
		}
	}
}
?>
