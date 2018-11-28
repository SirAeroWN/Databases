<?php
  header('Content-Type: application/json');

  $aResult = array();

  if( !isset($_POST['firstName']) ) { $aResult['error'] = 'Please enter your first name.'; }
  if( !isset($_POST['lastName']) ) { $aResult['error'] = 'Please enter your last name.'; }
  if( !isset($_POST['userEmail']) ) { $aResult['error'] = 'Please enter your email.'; }
  if( !isset($_POST['userPwd']) ) { $aResult['error'] = 'Please enter your password.'; }

  if( !isset($aResult['error']) ) {

    //change hardcoded creds if posted online
    $host     = "yingqing-4750.ctheaw88fxx7.us-east-1.rds.amazonaws.com";
    $port     = 3306;
    $socket   = "";
    $user     = "mariah";
    $password = "mariah";
    $dbname   = "Library";

    //create connection
    $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
        or die ('Could not connect to the database server' . mysqli_connect_error());

    if ($conn->connect_error) {
    $aResult['error'] = 'bad connection';
       //die("Connection failed: " . $conn->connect_error);

    }

    else{

      /* CREATE USER */
      $field = $_POST['searchTerm'];
      // vuln to sql injection
      if($field == 'title'){  // search by title
        $sql = "SELECT title, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name, publisher.company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN vendor ON sells.vendor_id = vendor.vendor_id
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        WHERE title = '{$_POST['input']}';";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
              echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nGenre (Dewey value): ".$row["dewey"]."\nCondition: ".$row["con"]."\nPublisher: ".$row["company_name"]."\n\n";
          }
        }
        else {
          echo "0 results";
        }
      }

    }

    $conn->close();
    }

    echo json_encode($aResult);

?>
