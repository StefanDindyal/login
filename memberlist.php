<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");
    
    // At the top of the page we check to see whether the user is logged in or not
    if(empty($_SESSION['user']))
    {
        // If they are not, we redirect them to the login page.
        header("Location: login.php");
        
        // Remember that this die statement is absolutely critical.  Without it,
        // people can view your members-only content without logging in.
        die("Redirecting to login.php");
    }
    
    // Everything below this point in the file is secured by the login system
    
    // We can retrieve a list of members from the database using a SELECT query.
    // In this case we do not have a WHERE clause because we want to select all
    // of the rows from the database table.
    $query = "
        SELECT
            id,
            username,
            email
        FROM users
    ";
    
    try
    {
        // These two statements run the query against your database table.
        $stmt = $db->prepare($query);
        $stmt->execute();
    }
    catch(PDOException $ex)
    {
        // Note: On a production website, you should not output $ex->getMessage().
        // It may provide an attacker with helpful information about your code. 
        die("Failed to run query: " . $ex->getMessage());
    }
        
    // Finally, we can retrieve all of the found rows into an array using fetchAll
    $rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<?php include 'include/header.php'; ?>

<body>

    <div id="page">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">
                        <img alt="Brand" src="...">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <p class="navbar-text navbar-right">Signed in as <a href="edit_account.php" class="navbar-link"><?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?></a></p>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="memberlist.php">Memberlist</a>
                        </li>
                        <li>
                            <a href="edit_account.php">Edit Account</a>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>                      
            </div>
        </nav>
        <div id="info">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Memberlist</h3>
                </div>
                <div class="panel-body">
                    <div class="container-fluid">                                            
                        <table class="table table-bordered">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>E-Mail Address</th>
                            </tr>
                            <?php foreach($rows as $row): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td> <!-- htmlentities is not needed here because $row['id'] is always an integer -->
                                    <td><?php echo htmlentities($row['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlentities($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <p><a href="private.php">Go Back</a></p>
                    </div>
                </div>
            </div>                
        </div>
    </div>

<?php include 'include/footer.php'; ?>

</body>
</html>