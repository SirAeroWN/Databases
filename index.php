<?php
session_start();
?>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Catalog</title>
</head>
<body>
  <font face="verdana">
<h2>Library Catalog</h2>

<p>Welcome to the library catalog! Here you can...</p>

<hr>

<h4>Search for Books</h4>

    <form action="search_page.php" name="search" method="post">

    <p>
    Please enter search term: <input type="text" name="input">
    <select name='searchTerm'>
    <option value='title'>Title</option>
    <option value='author'>Author</option>
    <option value='genre'>Genre</option>
    <option value='isbn'>ISBN</option>
    </select>
</p>

<input type="submit">
</form>
<br>

<hr>

<h4>Reserve a Book</h4>

<form action="reserve.php" name="reserve" method="post">
  <!-- <p>Email: <input type="text" name="reserveEmail"></p> -->
  <!-- <p>Password: <input type="text" name="reservePwd"></p> -->

  <p>Please enter the ISBN of the book you would like to reserve: <input type="text" name="reserveISBN"></p>
  <p><i>Don't know the ISBN of your book? Use the "Search for Books" feature above!</i></p>
<input type="submit">
</form>
<?php
    if (array_key_exists('last_reserved', $_SESSION)) {
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
            $sql = "SELECT distinct isbn_table.isbn, book_id, title, first_name, last_name, dewey, con, publisher.company_name AS company_name FROM isbn_table
                JOIN writes ON isbn_table.isbn = writes.isbn
                JOIN author ON writes.author_id = author.author_id
                JOIN book ON isbn_table.isbn = book.isbn
                JOIN sells ON isbn_table.isbn = sells.isbn
                JOIN publishes ON isbn_table.isbn = publishes.isbn
                JOIN publisher ON publishes.publisher_id = publisher.publisher_id
                where book_id = {$_SESSION['last_reserved']};";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                echo "<h3>You just reserved:</h3>";
                $row = $result->fetch_assoc();
                echo "<p>"."Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Condition: ".$row["con"]."</p>";
            }
        }
    }
?>
<br>

<hr>

<h4>Checkout a Book</h4>

<form action="test.php" name="checkout" method="post">
  <!-- <p>Email: <input type="text" name="checkoutEmail"></p> -->
  <!-- <p>Password: <input type="text" name="checkoutPwd"></p> -->

  <p>Please enter the ISBN of the book you would like to checkout: <input type="text" name="checkoutISBN"></p>
  <p><i>Don't know the ISBN of your book? Use the "Search for Books" feature above!</i></p>
<input type="submit">

</form>
<?php
    if (array_key_exists('last_checkedout', $_SESSION)) {
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
            $sql = "SELECT distinct isbn_table.isbn, book_id, title, first_name, last_name, dewey, con, publisher.company_name AS company_name FROM isbn_table
                JOIN writes ON isbn_table.isbn = writes.isbn
                JOIN author ON writes.author_id = author.author_id
                JOIN book ON isbn_table.isbn = book.isbn
                JOIN sells ON isbn_table.isbn = sells.isbn
                JOIN publishes ON isbn_table.isbn = publishes.isbn
                JOIN publisher ON publishes.publisher_id = publisher.publisher_id
                where book_id = {$_SESSION['last_checkedout']};";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                echo "<h3>You just checked out:</h3>";
                $row = $result->fetch_assoc();
                echo "<p>"."Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Condition: ".$row["con"]."</p>";
            }
        }
    }
?>
<br>

<hr>

<!-- show currently checked out books -->
<?php
    $pid = $_SESSION['pid'];

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
        // show checked out books
        echo "<h2>Your Checked Out Books</h2>";

        $sql = "SELECT distinct isbn_table.isbn, book_id, title, first_name, last_name, dewey, con, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        where book_id in (select book_id from checked_out where patron_id = {$pid})
        group by book_id;";

    
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
            echo "<p>"."Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Condition: ".$row["con"]."</p>";
          }
        } else {
            echo "<p>You've not checked out any books.</p>";
        }
        // show reserved out books
        echo "<h2>Your Reserved Books</h2>";

        $sql = "SELECT distinct isbn_table.isbn, book_id, title, first_name, last_name, dewey, con, publisher.company_name AS company_name FROM isbn_table
        JOIN writes ON isbn_table.isbn = writes.isbn
        JOIN author ON writes.author_id = author.author_id
        JOIN book ON isbn_table.isbn = book.isbn
        JOIN sells ON isbn_table.isbn = sells.isbn
        JOIN publishes ON isbn_table.isbn = publishes.isbn
        JOIN publisher ON publishes.publisher_id = publisher.publisher_id
        where book_id in (select book_id from reserves where patron_id = {$pid})
        group by book_id;";

    
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
            echo "<p>"."Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Condition: ".$row["con"]."</p>";
          }
        } else {
            echo "<p>You've not reserved any books.</p>";
        }
    $conn->close();
    }
?>
<br>
<p><i>Thank you for visiting, happy reading!</i></p>
<br>
</font>
</body>
</html>
