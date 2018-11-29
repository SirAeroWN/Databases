<?php
  header('Content-Type: application/json');

  $aResult = array();

  if( isset($_POST['checkoutISBN']) && !isset($_POST['checkoutEmail']) ) { $aResult['error'] = 'Please enter your email.'; }
  if( isset($_POST['checkoutISBN']) && !isset($_POST['checkoutPwd']) ) { $aResult['error'] = 'Please enter your password.'; }

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
            WHERE book.isbn = '{$reserveISBN}' AND book_id not in (SELECT book_id from reserves where patron_id <> {$pid}) AND book_id not in (SELECT book_id from checked_out) and con = '{$cond}'
            group by book_id;";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $bid = $row["book_id"];
            $sql = "insert into checked_out (book_id, patron_id, start_date) values({$bid}, {$pid}, now());";
            $result = $conn->query($sql);
            if ($result != TRUE) {
                echo "FAILED; ".$sql;
            } else {
            echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been checked out![".$row["book_id"]."]\n\n";
            $reserved = 1;
            }
        }
        return $reserved;
    }

    if ($conn->connect_error) {
    $aResult['error'] = 'bad connection';
       //die("Connection failed: " . $conn->connect_error);

    }

    else{

      /* CHECKOUT BOOKS */
      // vuln to sql injection
      if(isset($_POST['checkoutISBN'])){
        $sql = "SELECT email, pwd, patron_id as pid FROM patron
        WHERE email='{$_POST['checkoutEmail']}' AND pwd='{$_POST['checkoutPwd']}';";
        /* fill with query: if email & pwd are correct, set ONE of the
        available copies to checked out
        */

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            $pid = $result->fetch_assoc();
            $pid = $pid["pid"];
            $conditions = array('excellent', 'good', 'poor', 'bad');
            $reserved = 0;
            foreach ($conditions as $cond) {
                $reserved = reserve_isbn($pid, $_POST['checkoutISBN'], $cond, $conn);
                if ($reserved == 1) {
                    break 1;
                }
            }

            if($reserved == 0){ // for some reason this won't work!
              echo "Sorry, all copies of this book are checked out. Feel free to reserve a copy!";
            }
      }
      else {
          echo "Incorrect email or password.";
      }

    }

    $conn->close();
    }
  }

    echo json_encode($aResult);

?>
