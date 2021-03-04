<?php
$con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");

if (mysqli_connect_errno()) {
    echo "Failed to connect" . mysqli_connect_errno();
}

$query = mysqli_query($con, "INSERT INTO users VALUES(NULL,'George')");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

</body>

</html>