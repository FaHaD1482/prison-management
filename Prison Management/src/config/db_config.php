<?php

function getConnection()
{
    static $conn;

    if (!$conn) {
        $host = "localhost";
        $service = "XE";
        $username = "noboni";   // replace with your Oracle 10g XE DB username
        $password = "rt23er";   // replace with your Oracle 10g XE DB password

        $conn = oci_connect($username, $password, "localhost/XE");

        if (!$conn) {
            $error = oci_error();
            die("Could not connect to the database: " . $error['message']);
        }
    }

    return $conn;
}