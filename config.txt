<?php

    session_start();
    $con="";
    $db="cricauction";
    $server="localhost";
    $pwd="Root@123";
    $usr="root";
    $port=3306;

    $con=mysqli_connect($server,$usr,$pwd,$db,$port);
    if(mysqli_connect_errno())
    {
        echo "<script> alert('Failed to connect to Mysql'); </script>";
        exit();
    }

?>