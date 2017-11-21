<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Secret Santa Maker</title>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1" name="viewport">
<link
	href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
	rel="stylesheet">
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js">
	</script>
<script
	src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js">
	</script>
</head>
<style>
.jumbotron{background-color: #d42426!important;}
body {color:white;}
</style>
<?php
$givers = array(
    "Tova",
    "Autumn",
    "Melanie",
    "Sanam",
    "Jenelle"
);

// if user clicked log out, log them out
if (isset($_POST["logout"]) && $_POST["logout"]) {
    session_unset();
    session_destroy();
}

if (isset($_POST["shuffle"])) {
    $allRecipients = "";
    $recipientFile = fopen("recipients", "r");
    while (! feof($recipientFile)) {
        $line = fgets($recipientFile);
        if (! $line == "") {
            $recipients[] = $line;
        }
    }
    // print_r($recipients);
    
    fclose($recipientFile);
    shuffle($recipients);
    $i = 0;
    while ($i < (count($recipients))) {
        if ($givers[$i] === trim($recipients[$i])) {
            shuffle($recipients);
            $i = 0;
        } else {
            $i ++;
        }
    }
    
    foreach ($recipients as $tempRecipient) {
        $allRecipients .= $tempRecipient;
    }
    
    file_put_contents ("recipients" , $allRecipients);
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    showmatches($givers);
    // if user is trying to log in, validate their credentials
} else if ((isset($_POST["username"])) || (isset($_POST["password"]))) {
    checkpassword($givers);
} else {
    // show login page
    showlogin();
}

function readRecipients($givers) {
    $recipientFile = fopen("recipients", "r");
    while (! feof($recipientFile)) {
        $line = fgets($recipientFile);
        $recipients[] = $line;
    }
    fclose($recipientFile);
    return $recipients;
}

// function to show login page
function showlogin()
{
    echo "<div class='container'>";
    echo '<div class="jumbotron">';
    echo '<h3><span class="glyphicon glyphicon-gift"></span></h3>';
    echo "<h3>Log in to get your secret santa match!</h3>";
    echo "<form action='SecretSanta.php' method='post'>";
    echo "<div class='form-group'>";
    echo "<label for='username'>Username</label>";
    echo "<input class='form-control' name='username' placeholder='Username' />";
    echo "</div>";
    echo "<div class='form-group'>";
    echo "<label for='password'>Password</label>";
    echo "<input class='form-control' name='password' placeholder='Password' type='password' />";
    echo "</div>";
    echo "<br />";
    echo "<button class='btn btn-success' type='submit'><span class='glyphicon glyphicon-log-in'></span> Log In</button>";
    if (isset($_SESSION['warning']) && $_SESSION['warning'] == true) {
        echo "<p class='text text-center'>Invalid login credentials</p>";
    }
    echo "</form>";
    echo "</div>";
    echo "</div>";
    
}

// function to validate username and password
function checkpassword($givers)
{
    $file = fopen("credentials.config", "r");
    $correct = false;
    $_SESSION['warning'] = false;
    $username = $_POST["username"];
    $password = $_POST["password"];
    if (! (strlen($username) == 0 || strlen($password) == 0)) {
        while (! (feof($file)) && $correct == false) {
            $line = fgets($file);
            $parts = array_map("trim", explode(",", $line));
            if (($parts[0] === $username) && ($parts[1] === $password)) {
                $_SESSION['username'] = $username;
                $_SESSION['loggedin'] = true;
                $correct = true;
                showmatches($givers);
            }
        }
    }
    if ($correct == false) {
        $_SESSION['warning'] = true;
        fclose($file);
        header("Location: SecretSanta.php");
        die();
    }
}


// math game function
function showmatches($givers)
{
    echo '<div class="container">';
    echo '<div class="jumbotron">';
    echo '<h4>Hello ' . $_SESSION["username"] . '!</h4>';
    echo '<h4><span class="glyphicon glyphicon-tree-conifer"></span><span class="glyphicon glyphicon-tree-conifer"></span><span class="glyphicon glyphicon-tree-conifer"></span><span class="glyphicon glyphicon-tree-conifer"></span><span class="glyphicon glyphicon-tree-conifer"></span>';
    $index = array_search($_SESSION["username"], $givers);
    $recipients = readRecipients($givers);
    if ($index !== false) {
        echo '<h4>Your match is ' . trim($recipients[$index]) . '. Merry Christmas!</h4>';
    }
    if ($_SESSION["username"] === "Admin") {
        if (isset($_POST["shuffle"]) && $_POST["shuffle"] === "true") {
            echo '<h4>Participants shuffled.</h4>';
        }
        echo '<form action="SecretSanta.php" method="post">';
        echo '<div class="form-group">';
        echo '<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-random"></span> Shuffle</button>';
        echo '<input type="hidden" name="shuffle" value="true" />';
        echo '</div>';
        echo '</form>';
        
        echo '<form action="SecretSanta.php" method="post">';
        echo '<div class="form-group">';

        
        if (isset($_POST["show"]) && $_POST["show"] === "true") { 
            echo '<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-eye-close"></span> Hide all matches</button>';
            echo '<input type="hidden" name="show" value="false" />';
            echo "<div class='container'>";
            $recipients = readRecipients($givers);
            for ($i = 0; $i < count($givers); $i ++) {
                echo '<h5>' . $givers[$i] . " : " . $recipients[$i] . "</h5>";
            }
            echo "</div>";
        } else {
            echo '<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-eye-open"></span> Show all matches</button>';
            echo '<input type="hidden" name="show" value="true" />';
        }
        
        echo '</div>';
        echo '</form>';
        
    }
    echo '<form action="SecretSanta.php" method="post">';
    echo '<div class="form-group">';
    echo '<div id="submitButton">';
    echo '<button class="btn btn-success" type="submit"><span class="glyphicon glyphicon-log-out"></span> Log Out</button>';
    echo '<input type="hidden" name="logout" value="true" />';
    echo '</div>';
    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
}

?>
</html>