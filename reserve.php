<?php
  header('Content-Type: application/json');

  $aResult = array();

  if( isset($_POST['reserveISBN']) && !isset($_POST['reserveEmail']) ) { $aResult['error'] = 'Please enter your email.'; }
  if( isset($_POST['reserveISBN']) && !isset($_POST['reservePwd']) ) { $aResult['error'] = 'Please enter your password.'; }

  if( !isset($aResult['error']) ) {

    //change hardcoded creds if posted online
    $host     = "yingqing-4750.ctheaw88fxx7.us-east-1.rds.amazonaws.com";
    $port     = 3306;
    $socket   = "";
    $user     = "";
    $password = "";
    $dbname   = "Library";

    //create connection
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die ('Could not connect to the database server' . mysqli_connect_error());

    if ($conn->connect_error) {
    $aResult['error'] = 'bad connection';
       //die("Connection failed: " . $conn->connect_error);

    }

    else{

      /* RESERVE BOOKS */
      // vuln to sql injection
      if(isset($_POST['reserveISBN'])){
        $sql = "SELECT email, pwd FROM patron
        WHERE email='{$_POST['reserveEmail']}' AND pwd='{$_POST['reservePwd']}';";
        /* fill with query: if email & pwd are correct, set ONE of the
        available copies to reserved
        conditions: bad, poor, good, excellent
        */

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            $reserved = 0;
            $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table
            JOIN writes ON isbn_table.isbn = writes.isbn
            JOIN author ON writes.author_id = author.author_id
            JOIN book ON isbn_table.isbn = book.isbn
            JOIN sells ON isbn_table.isbn = sells.isbn
            WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'excellent';";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                  echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!";
                }
                $reserved = 1;
            }
            if($reserved == 0){
              $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table
              JOIN writes ON isbn_table.isbn = writes.isbn
              JOIN author ON writes.author_id = author.author_id
              JOIN book ON isbn_table.isbn = book.isbn
              JOIN sells ON isbn_table.isbn = sells.isbn
              WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'good';";

              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  // output data of each row
                  while($row = $result->fetch_assoc()) {
                    echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!";
                  }
                  $reserved = 1;
              }
            }
            elseif($reserved == 0){
              $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table
              JOIN writes ON isbn_table.isbn = writes.isbn
              JOIN author ON writes.author_id = author.author_id
              JOIN book ON isbn_table.isbn = book.isbn
              JOIN sells ON isbn_table.isbn = sells.isbn
              WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'poor';";

              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  // output data of each row
                  while($row = $result->fetch_assoc()) {
                    echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!";
                  }
                  $reserved = 1;
              }
            }
            elseif($reserved == 0){
              $sql = "SELECT DISTINCT title, first_name, last_name, isbn_table.isbn, con FROM isbn_table
              JOIN writes ON isbn_table.isbn = writes.isbn
              JOIN author ON writes.author_id = author.author_id
              JOIN book ON isbn_table.isbn = book.isbn
              JOIN sells ON isbn_table.isbn = sells.isbn
              WHERE isbn_table.isbn = '{$_POST['reserveISBN']}' AND con = 'bad';";

              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                  // output data of each row
                  while($row = $result->fetch_assoc()) {
                    echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nCondition: ".$row["con"]."\n\nHas been reserved!";
                  }
                  $reserved = 1;
              }
            }
            elseif($reserved == 0){ // for some reason this won't work!
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
