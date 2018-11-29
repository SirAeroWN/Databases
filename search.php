<?php
  header('Content-Type: application/json');

  $aResult = array();

  if( !isset($_POST['searchTerm']) ) { $aResult['error'] = 'Please enter a search term.'; }

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

    }

    else{

      /* SEARCH BOOKS */
      $field = $_POST['searchTerm'];
      // vuln to sql injection
      if($field == 'title'){  // search by title
        $sql = "SELECT distinct isbn_table.isbn, book_id, title, first_name, last_name, dewey, con, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        WHERE MATCH (title) AGAINST ('{$_POST['input']}')
        group by title;";

    
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
            echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nGenre (Dewey value): ".$row["dewey"]."\nCondition: ".$row["con"]."\nPublisher: ".$row["company_name"]."\n\n";
          }
        }
        else {
          echo "0 results ".$sql;
        }
      }

      // vuln to sql injection
      if($field == 'author'){ //search by author's last name (currently)
        $sql = "SELECT title, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN vendor ON sells.vendor_id = vendor.vendor_id
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        WHERE last_name = '{$_POST['input']}';";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nGenre (Dewey value): ".$row["dewey"]."\nCondition: ".$row["con"]."\nVendor: ".$row["v_company_name"]."\nPublisher: ".$row["company_name"]."\n\n";
            }
        }
        else {
            echo "0 results";
        }
      }
      // vuln to sql injection
      if($field == 'genre'){  // search by genre
        $sql = "SELECT title, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN vendor ON sells.vendor_id = vendor.vendor_id
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        WHERE dewey = '{$_POST['input']}';";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nGenre (Dewey value): ".$row["dewey"]."\nCondition: ".$row["con"]."\nVendor: ".$row["v_company_name"]."\nPublisher: ".$row["company_name"]."\n\n";
            }
        }
        else {
            echo "0 results";
        }
      }
      // vuln to sql injection
      if($field == 'ISBN'){   //search by ISBN value
        $sql = "SELECT title, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN vendor ON sells.vendor_id = vendor.vendor_id
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        WHERE isbn_table.isbn = '{$_POST['input']}';";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
              echo "Book Title: ".$row["title"]."\nAuthor: ".$row["first_name"]." ".$row["last_name"]."\nISBN: ".$row["isbn"]."\nGenre (Dewey value): ".$row["dewey"]."\nCondition: ".$row["con"]."\nVendor: ".$row["v_company_name"]."\nPublisher: ".$row["company_name"]."\n\n";
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
