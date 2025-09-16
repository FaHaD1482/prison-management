<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Static login
    if ($username === 'noboni' && $password === '1234') {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: ../../src/php/overview.php");
    } else {
        echo "Invalid credentials.";
    }

    exit();
}
?>