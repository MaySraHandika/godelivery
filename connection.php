<?php
$connection = null;
try {
    //config
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "godelivery";

    //connect
    $database = "mysql:dbname=$dbname;host=$host";
    $connection = new PDO($database, $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($connection) {
        echo "Koneksi Berhasil";
    } else {
        echo "Error";
    }

} catch (PDOException $e) {
    echo "Error ! " . $e->getMessage();
    die;
}