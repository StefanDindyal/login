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
    
    // We can display the user's username to them by reading it from the session array.  Remember that because
    // a username is user submitted content we must use htmlentities on it before displaying it to the user.
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
        <div id="login">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Hello <?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?>, secret content!</h3>
                </div>
                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="memberlist.php">Memberlist</a><br />
                                <a href="edit_account.php">Edit Account</a><br />
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'include/footer.php'; ?>

</body>
</html>