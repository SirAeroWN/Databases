<?php
header('Content-Type: application/json');

$aResult = array();

if( !isset($_POST['firstName']) ) { $aResult['error'] = 'Please enter your first name.'; }
if( !isset($_POST['lastName']) ) { $aResult['error'] = 'Please enter your last name.'; }
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
    } else{
        // check if email already exists
        $sql = "select email from patron where email = '{$_POST['userEmail']}';";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // a user already exists so fail
            header('Location: failed.html');
        } else {
            // now we can create a user
            $sql = "insert into patron (first_name, last_name, email, pwd) values('{$_POST['firstName']}', '{$_POST['lastName']}', '{$_POST['userEmail']}', '{$_POST['userPwd']}');";

            $result = $conn->query($sql);
            if ($result != TRUE) {
                header('Location: failed2.html');
            } else {
                header('Location: login.html');
            }
        }
    }

    $conn->close();
}
?>
