<?php
session_unset();
session_destroy();

header('Content-Type: application/json');

$aResult = array();

if( !isset($_POST['userEmail']) ) { $aResult['error'] = 'Please enter your email.'; }
if( !isset($_POST['userPwd']) ) { $aResult['error'] = 'Please enter your password.'; }

if( !isset($aResult['error']) ) {

    //change hardcoded creds if posted online
    /* $host     = "yingqing-4750.ctheaw88fxx7.us-east-1.rds.amazonaws.com"; */
    /* $port     = 3306; */
    /* $socket   = ""; */
    /* $user     = "mariah"; */
    /* $password = "mariah"; */
    /* $dbname   = "Library"; */
    $host     = "localhost";
    $port     = 3306;
    $socket   = "";
    $user     = "root";
    $password = "password";
    $dbname   = "Library";

    //create connection
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die ('Could not connect to the database server' . mysqli_connect_error());

    if ($conn->connect_error) {
        $aResult['error'] = 'bad connection';
        //die("Connection failed: " . $conn->connect_error);
    } else {
        $sql = "SELECT email, pwd, patron_id as pid FROM patron
            WHERE email='{$_POST['userEmail']}' AND pwd='{$_POST['userPwd']}';";

        $result = $conn->query($sql);

            /* session_start(); */
            /* $_SESSION['pid'] = $result->fetch_assoc()['pid']; */
        /* echo "pid=".$_SESSION['pid']."\n\n"; */
        if ($result->num_rows > 0) {
            echo session_start();
            $_SESSION['pid'] = $result->fetch_assoc()['pid'];
            header('Location: index.php');
        } else {
            echo $result->num_rows.";".$sql;
        }
    }
    $conn->close();
}
?>
