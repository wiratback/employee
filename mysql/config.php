<?php
date_default_timezone_set("Asia/Bangkok");
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "employee_data";

$connStr = "mysql:host=$db_host; dbname=$db_name; charset=UTF8";
$conn = new PDO($connStr,$db_user,$db_pass);

// $conn = new PDO('mysql:host=localhost;dbname=test', $user, $pass);

$departments = array('none','ฝ่ายบริหาร','ฝ่ายการตลาด','ฝ่ายบัญชี','ฝ่ายลูกค้าสัมพันธ์','ฝ่ายบริการหลังการขาย');