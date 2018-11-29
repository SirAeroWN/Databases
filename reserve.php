<?php
  header('Content-Type: application/json');

  $aResult = array();

  if( isset($_POST['reserveISBN']) && !isset($_POST['reserveEmail']) ) { $aResult['error'] = 'Please enter your email.'; }
  if( isset($_POST['reserveISBN']) && !isset($_POST['reservePwd']) ) { $aResult['error'] = 'Please enter your password.'; }

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
            echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved![".$row["book_id"]."]\n\n";
            $reserved = 1;
            $bid = $row["book_id"];
            $sql = "insert into reserves (book_id, patron_id) values({$bid}, {$pid});";
            $result = $conn->query($sql);
            if ($result != TRUE) { echo "FAILED"; }
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
      if(isset($_POST['reserveISBN'])){
        $sql = "SELECT email, pwd, patron_id as pid FROM patron
        WHERE email='{$_POST['reserveEmail']}' AND pwd='{$_POST['reservePwd']}';";
        /* fill with query: if email & pwd are correct, set ONE of the
        available copies to reserved
        conditions: bad, poor, good, excellent
        */

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            $pid = $result->fetch_assoc();
            $pid = $pid["pid"];
            $conditions = array('excellent', 'good', 'poor', 'bad');
            $reserved = 0;
            foreach ($conditions as $cond) {
                $reserved = reserve_isbn($pid, $_POST['reserveISBN'], $cond, $conn);
                if ($reserved == 1) {
                    break 1;
                }
            }

/*             if($reserved == 0){ */
/*                 $reserved = reserve_isbn($pid, $_POST['reserveISBN'], 'good', $conn); */
/*             } */
/*             elseif($reserved == 0){ */
/*               $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table */
/*               JOIN writes ON isbn_table.isbn = writes.isbn */
/*               JOIN author ON writes.author_id = author.author_id */
/*               JOIN book ON isbn_table.isbn = book.isbn */
/*               JOIN sells ON isbn_table.isbn = sells.isbn */
/*               WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'poor';"; */

/*               $result = $conn->query($sql); */

/*               if ($result->num_rows > 0) { */
/*                   // output data of each row */
/*                   while($row = $result->fetch_assoc()) { */
/*                     echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!"; */
/*                   } */
/*                   $reserved = 1; */
/*               } */
/*             } */
/*             elseif($reserved == 0){ */
/*               $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table */
/*               JOIN writes ON isbn_table.isbn = writes.isbn */
/*               JOIN author ON writes.author_id = author.author_id */
/*               JOIN book ON isbn_table.isbn = book.isbn */
/*               JOIN sells ON isbn_table.isbn = sells.isbn */
/*               WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'bad';"; */

/*               $result = $conn->query($sql); */

/*               if ($result->num_rows > 0) { */
/*                   // output data of each row */
/*                   while($row = $result->fetch_assoc()) { */
/*                     echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!"; */
/*                   } */
/*                   $reserved = 1; */
/*               } */
/*             } */
            if($reserved == 0){ // for some reason this won't work!
              echo "Sorry, we were unable to reserve this book. Please check to make sure you have entered a valid ISBN value.";
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
