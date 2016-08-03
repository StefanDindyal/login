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
    
    // This if statement checks to determine whether the edit form has been submitted
    // If it has, then the account updating code is run, otherwise the form is displayed
    if(!empty($_POST))
    {
        // Make sure the user entered a valid E-Mail address
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        {
            die("Invalid E-Mail Address");
        }
        
        // If the user is changing their E-Mail address, we need to make sure that
        // the new value does not conflict with a value that is already in the system.
        // If the user is not changing their E-Mail address this check is not needed.
        if($_POST['email'] != $_SESSION['user']['email'])
        {
            // Define our SQL query
            $query = "
                SELECT
                    1
                FROM users
                WHERE
                    email = :email
            ";
            
            // Define our query parameter values
            $query_params = array(
                ':email' => $_POST['email']
            );
            
            try
            {
                // Execute the query
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            }
            catch(PDOException $ex)
            {
                // Note: On a production website, you should not output $ex->getMessage().
                // It may provide an attacker with helpful information about your code. 
                die("Failed to run query: " . $ex->getMessage());
            }
            
            // Retrieve results (if any)
            $row = $stmt->fetch();
            if($row)
            {
                die("This E-Mail address is already in use");
            }
        }
        
        // If the user entered a new password, we need to hash it and generate a fresh salt
        // for good measure.
        if(!empty($_POST['password']))
        {
            $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
            $password = hash('sha256', $_POST['password'] . $salt);
            for($round = 0; $round < 65536; $round++)
            {
                $password = hash('sha256', $password . $salt);
            }
        }
        else
        {
            // If the user did not enter a new password we will not update their old one.
            $password = null;
            $salt = null;
        }
        
        // Initial query parameter values
        $query_params = array(
            ':email' => $_POST['email'],
            ':user_id' => $_SESSION['user']['id'],
        );
        
        // If the user is changing their password, then we need parameter values
        // for the new password hash and salt too.
        if($password !== null)
        {
            $query_params[':password'] = $password;
            $query_params[':salt'] = $salt;
        }
        
        // Note how this is only first half of the necessary update query.  We will dynamically
        // construct the rest of it depending on whether or not the user is changing
        // their password.
        $query = "
            UPDATE users
            SET
                email = :email
        ";
        
        // If the user is changing their password, then we extend the SQL query
        // to include the password and salt columns and parameter tokens too.
        if($password !== null)
        {
            $query .= "
                , password = :password
                , salt = :salt
            ";
        }
        
        // Finally we finish the update query by specifying that we only wish
        // to update the one record with for the current user.
        $query .= "
            WHERE
                id = :user_id
        ";
        
        try
        {
            // Execute the query
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        }
        catch(PDOException $ex)
        {
            // Note: On a production website, you should not output $ex->getMessage().
            // It may provide an attacker with helpful information about your code. 
            die("Failed to run query: " . $ex->getMessage());
        }
        
        // Now that the user's E-Mail address has changed, the data stored in the $_SESSION
        // array is stale; we need to update it so that it is accurate.
        $_SESSION['user']['email'] = $_POST['email'];
        
        // This redirects the user back to the members-only page after they register
        header("Location: private.php");
        
        // Calling die or exit after performing a redirect using the header function
        // is critical.  The rest of your PHP script will continue to execute and
        // will be sent to the user if you do not die or exit.
        die("Redirecting to private.php");
    }
    
?>
<!DOCTYPE html>
<html>

<?php include 'include/header.php'; ?>

<body>

    <div id="page">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="private.php">
                        <img alt="Brand" src="...">
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <p class="navbar-text navbar-right">Signed in as <a href="edit_account.php" class="navbar-link"><?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?></a></p>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a href="memberlist.php">Memberlist</a>
                        </li>
                        <li class="active">
                            <a href="edit_account.php">Edit Account</a>
                        </li>
                        <li>
                            <a href="logout.php">Logout</a>
                        </li>
                    </ul>
                </div>                      
            </div>
        </nav>
        <div id="login">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Edit Account</h3>
                </div>
                <div class="panel-body">
                    <div class="container-fluid">                        
                        <form action="edit_account.php" method="post" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Username:</label>
                                <div class="col-sm-10">
                                    <p class="form-control-static"><?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>                                   
                            <div class="form-group">
                                <label for="email" class="col-sm-2 control-label">Email:</label>
                                <div class="col-sm-10">
                                    <input type="text" name="email" id="email" value="<?php echo htmlentities($_SESSION['user']['email'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control"/>                                           
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-sm-2 control-label">Password:</label>
                                <div class="col-sm-10">
                                    <input type="password" name="password" id="password" value="" class="form-control"/>
                                    <span id="helpBlock" class="help-block">leave blank if you do not want to change your password</span>
                                </div>                                
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>                                    
                        </form>                            
                    </div>
                </div>
            </div>
        </div>
    </div>    

<?php include 'include/footer.php'; ?>

</body>
</html>