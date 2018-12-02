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
<a href="index.php">Home</a>
<br>

<hr>

<?php
if( !isset($_POST['searchTerm']) ) { $aResult['error'] = 'Please enter a search term.'; }

if( !isset($aResult['error']) ) {

    function action_forms($row, $pid, $conn) {
        // echo a checkout form if this can be checked out
        $sql = "select isbn from book
            where {$row['book_id']} not in (SELECT book_id from reserves where patron_id <> {$pid}) AND {$row['book_id']} not in (SELECT book_id from checked_out);";

        $sub_result = $conn->query($sql);

        if ($sub_result->num_rows > 0) {
            $sub_row = $sub_result->fetch_assoc();
            // now actually echo the form
            echo "<form action=\"test.php\" name=\"checkout\" method=\"post\">";
            echo "<input name='checkoutISBN' type='hidden' value='{$row['isbn']}'>";
            echo "<input type=\"submit\" value=\"Checkout\">";
            echo "</form>";
        } else {
            echo "<br>[Checked Out]";
        }
        // echo a reserve form if this can be reserved
        $sql = "select isbn from book
            where {$row['book_id']} not in (SELECT book_id from reserves where patron_id <> {$pid});";

        $sub_result = $conn->query($sql);

        if ($sub_result->num_rows > 0) {
            $sub_row = $sub_result->fetch_assoc();
            // now actually echo the form
            echo "<form action=\"reserve.php\" name=\"reserve\" method=\"post\">";
            echo "<input name='reserveISBN' type='hidden' value='{$row['isbn']}'>";
            echo "<input type=\"submit\" value=\"Reserve\">";
            echo "</form>";
            // echo a reserve form if this can be reserved
        } else {
            echo "<br>[Already Reserved]";
        }
    }

    $pid = $_SESSION['pid'];

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
                group by book_id;";
            /* group by title;"; */


            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<p>Book Title: <b>".$row["title"]."</b><br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Genre (Dewey value): ".$row["dewey"]."<br>Condition: ".$row["con"]."<br>Publisher: ".$row["company_name"]."<br>Book ID: ".$row['book_id']."</p>";
                    action_forms($row, $pid, $conn);
                    echo "<hr>";
                }
            } else {
                echo "0 results ".$sql;
            }
        }

        // vuln to sql injection
        if($field == 'author'){ //search by author's last name (currently)
            $sql = "SELECT title, book_id, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
                JOIN writes ON isbn_table.isbn = writes.isbn
                JOIN author ON writes.author_id = author.author_id
                JOIN book ON isbn_table.isbn = book.isbn
                JOIN sells ON isbn_table.isbn = sells.isbn
                JOIN vendor ON sells.vendor_id = vendor.vendor_id
                JOIN publishes ON isbn_table.isbn = publishes.isbn
                JOIN publisher ON publishes.publisher_id = publisher.publisher_id
                WHERE last_name like '%{$_POST['input']}%';";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "</p>Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Genre (Dewey value): ".$row["dewey"]."<br>Condition: ".$row["con"]."<br>Publisher: ".$row["company_name"]."</p>";
                    action_forms($row, $pid, $conn);
                    echo "<hr>";
                }
            }
            else {
                echo "0 results";
            }
        }
        // vuln to sql injection
        if($field == 'genre'){  // search by genre
            $sql = "SELECT title, book_id, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
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
                    echo "</p>Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Genre (Dewey value): ".$row["dewey"]."<br>Condition: ".$row["con"]."<br>Publisher: ".$row["company_name"]."</p>";
                    action_forms($row, $pid, $conn);
                }
            }
            else {
                echo "0 results";
            }
        }
        // vuln to sql injection
        if($field == 'ISBN'){   //search by ISBN value
            $sql = "SELECT title, book_id, first_name, last_name, isbn_table.isbn, dewey, con, vendor.company_name AS v_company_name, publisher.company_name AS company_name FROM isbn_table
                JOIN writes ON isbn_table.isbn = writes.isbn
                JOIN author ON writes.author_id = author.author_id
                JOIN book ON isbn_table.isbn = book.isbn
                JOIN sells ON isbn_table.isbn = sells.isbn
                JOIN vendor ON sells.vendor_id = vendor.vendor_id
                JOIN publishes ON isbn_table.isbn = publishes.isbn
                JOIN publisher ON publishes.publisher_id = publisher.publisher_id
                WHERE isbn_table.isbn like '%{$_POST['input']}%';";

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "</p>Book Title: ".$row["title"]."<br>Author: ".$row["first_name"]." ".$row["last_name"]."<br>ISBN: ".$row["isbn"]."<br>Genre (Dewey value): ".$row["dewey"]."<br>Condition: ".$row["con"]."<br>Publisher: ".$row["company_name"]."</p>";
                    action_forms($row, $pid, $conn);
                    echo "<hr>";
                }
            }
            else {
                echo "0 results";
            }
        }

    }

    $conn->close();
}

?>
</font>
</body>
</html>
