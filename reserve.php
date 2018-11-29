<?php
header('Content-Type: application/json');

session_start();

$aResult = array();

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

    function reserve_isbn($pid, $reserveISBN, $cond, $conn) {
        $reserved = 0;
        $sql = "SELECT book_id, title, first_name, last_name, isbn_table.isbn, con FROM isbn_table
            JOIN writes ON isbn_table.isbn = writes.isbn
            JOIN author ON writes.author_id = author.author_id
            JOIN book ON isbn_table.isbn = book.isbn
            JOIN sells ON isbn_table.isbn = sells.isbn
            WHERE book.isbn = '{$reserveISBN}' AND book_id not in (SELECT book_id from reserves) and con = '{$cond}'
            group by book_id;";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            $row = $result->fetch_assoc();
            $bid = $row["book_id"];
            $sql = "insert into reserves (book_id, patron_id) values({$bid}, {$pid});";
            $result = $conn->query($sql);
            if ($result != TRUE) {
                error_log($sql, 0);
                error_log($result, 0);
            } else {
                echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved![".$row["book_id"]."]\n\n";
                $reserved = 1;
                error_log($reserveISBN, 0);
                $_SESSION['last_reserved'] = $bid;
            }
        }
        return $reserved;
    }

    if ($conn->connect_error) {
        $aResult['error'] = 'bad connection';
        //die("Connection failed: " . $conn->connect_error);

    }

    else{

        /* RESERVE BOOKS */
        // vuln to sql injection
        if(isset($_POST['reserveISBN'])) {
            $pid = $_SESSION['pid'];
            $conditions = array('excellent', 'good', 'poor', 'bad');
            $reserved = 0;
            foreach ($conditions as $cond) {
                $reserved = reserve_isbn($pid, $_POST['reserveISBN'], $cond, $conn);
                if ($reserved == 1) {
                    break 1;
                }
            }

            if($reserved == 0) {
                echo "Sorry, we were unable to reserve this book. All copies may already be reserved, also please check to make sure you have entered a valid ISBN value.";
            }
        }

        $conn->close();
    }
}

/* echo json_encode($aResult); */
header('Location: index.php');

?>
