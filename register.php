<?php
$con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");

if (mysqli_connect_errno()) {
    echo "Failed to connect" . mysqli_connect_errno();
}

// Declaring variables to prevent errors
$reg_fname = "";
$reg_lname = "";
$reg_email = "";
$reg_email_confirm = "";
$reg_password = "";
$reg_password_confirm = "";
$date = "";
$error_array = "";

// If submit button has been pressed
if (isset($_POST['register_button'])) {
    $reg_fname = strip_tags($_POST['reg_fname']);
    $reg_fname = str_replace(' ', '', $reg_fname);
    $reg_fname = ucfirst(strtolower($reg_fname));

    $reg_lname = strip_tags($_POST['reg_lname']);
    $reg_lname = str_replace(' ', '', $reg_lname);
    $reg_lname = ucfirst(strtolower($reg_lname));

    $reg_email = strip_tags($_POST['reg_email']);
    $reg_email = str_replace(' ', '', $reg_email);
    $reg_email = ucfirst(strtolower($reg_email));

    $reg_email_confirm = strip_tags($_POST['reg_email_confirm']);
    $reg_email_confirm = str_replace(' ', '', $reg_email_confirm);
    $reg_email_confirm = ucfirst(strtolower($reg_email_confirm));

    $reg_password = strip_tags($_POST['reg_password']);
    $reg_password_confirm = strip_tags($_POST['reg_password_confirm']);

    $date = date("Y-m-d"); // Current date

    if ($reg_email == $reg_email_confirm) {
        if (filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
            $reg_email = filter_var($reg_email, FILTER_VALIDATE_EMAIL);

            // Check if email exists
            $email_check = mysqli_query($con, "SELECT email FROM users WHERE email='$reg_email'");

            // Count number of rows returned
            $num_rows = mysqli_num_rows($email_check);

            if ($num_rows > 0) {
                echo "Email already in use";
            }
        } else {
            echo "Invalid email format";
        }
    } else {
        echo "Emails don't match";
    }

    if (strlen($reg_fname) > 25 || strlen($reg_fname) < 2) {
        echo "Your first name must be between 2 and 25 characters";
    }

    if (strlen($reg_lname) > 25 || strlen($reg_lname) < 2) {
        echo "Your first name must be between 2 and 25 characters";
    }

    if ($reg_password != $reg_password_confirm) {
        echo "Passwords don't match";
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $reg_password)) {
            echo "Your password can only contain english characters or numbers";
        }
    }

    if (strlen($reg_password) > 30 || strlen($reg_password) < 5) {
        echo "Your password must be between 5 and 30 characters";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="register.php" method="POST">
        <input type="text" name="reg_fname" placeholder="First Name" required>
        <input type="text" name="reg_fname" placeholder="First Name" required>
        <input type="email" name="reg_email" placeholder="Email" required>
        <input type="email" name="reg_email_confirm" placeholder="Cofirm email" required>
        <input type="password" name="reg_password" placeholder="Password" required>
        <input type="password" name="reg_password_confirm" placeholder="Confirm Password" required>
        <input type="submit" name="register_button" value="Register">
    </form>
</body>

</html>