<?php

    include('./includes/functions.php');

    $error = "";
    
    if(isset($_POST['news_title']) && isset($_POST['news_text']) &&
        isset($_POST['news_author']) && isset($_POST['news_author_id']) &&
        isset($_SESSION['user_name']) && isset($_SESSION['user_id'])) {
        $news_author = $_POST['news_author'];
        $news_author_id = $_POST['news_author_id'];
        $user_name = $_SESSION['user_name'];
        $user_id = $_SESSION['user_id'];
        $news_title = $_POST['news_title'];
        $news_text = $_POST['news_text'];
        if($user_name == $news_author && $user_id == $news_author_id) {
            if($news_title != "" && $news_text != "") {
                $news_title = safetify_input($news_title);
                $news_text = convert_to_html(safetify_input($news_text));
                $query = "INSERT INTO news(news_author, news_author_id, ".
                         "news_title, news_text, news_date) ".
                         "VALUES('$news_author', '$news_author_id', '$news_title', ".
                         "'$news_text', NOW())";
                if(mysqli_insert($query)) {
                } else {
                    $error = "Database error during submission.";
                }
            } else {
                $error = "Must include both a title and text.";
            }
        } else {
            $error = "Something's fishy, try again?";
        }
    }

    render_header("Thieves Tavern News");

    if(!isset($_GET['news_id'])) {
        if($error != "") {
            echo "<p class='error'>$error</p>\n";
        }
        $query = "SELECT * FROM news ORDER BY news_date DESC";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $news_id = $row['news_id'];
                $news_text = $row['news_text'];
                $news_date = $row['news_date'];
                $news_title = $row['news_title'];
                $news_author = $row['news_author'];
                $news_author_id = $row['news_author_id'];
                $news_author = "<a href='./profile.php?id=$news_author_id'>$news_author</a>";
                if(strlen($news_text) > 255) {
                    $news_text = substr($news_text, 0, 255) . "<a href='./news.php?news_id=$news_id'>...</a>";
                }
                echo "<div id='big_news_bulletin'>\n";
                echo "<p class='news_title'><a href='./news.php?news_id=$news_id'>$news_title</a></p>\n";
                echo "<p class='news_text'>$news_text</p>\n";
                echo "<span class='news_date'>By $news_author on $news_date</span>\n";
                echo "</div>\n";
            }
        }
        if(isset($_SESSION['user_name']) && isset($_SESSION['user_id'])) {
            $user_name = $_SESSION['user_name'];
            $user_id = $_SESSION['user_id'];
            if(is_user_admin($user_id)) {
                //Show news post thingy
                echo "<div id='news_post_form'>\n";
                echo "<h3>Post a news story</h3>\n";
                echo "<form method='POST' action='./news.php'>\n";
                echo "<label>Author: $user_name</label>\n";
                echo "<br />\n";
                echo "<label>Title: </label>\n";
                echo "<input type='text' name='news_title' size='60' ";
                if(isset($_POST['news_title']) && $error != "") {
                    echo "value='$_POST[news_title]' ";
                }
                echo "/>\n";
                echo "<br />\n";
                echo "<label>Text: </label>\n";
                echo "<textarea name='news_text' rows='10' cols='60'>";
                if(isset($_POST['news_text']) && $error != "") {
                    echo $_POST['news_text'];
                }
                echo "</textarea>\n";
                echo "<br />\n";
                echo "<input type='hidden' name='news_author' value='$user_name'>\n";
                echo "<input type='hidden' name='news_author_id' value='$user_id'>\n";
                echo "<input type='submit' class='submit' value='Post News' />\n";
                echo "</form>\n";
                echo "</div>\n"; //Close news_post_form
            }
        }
    } else {
        $news_id = $_GET['news_id'];
        $query = "SELECT * FROM news WHERE news_id='$news_id'";
        if($row = mysqli_get_one($query)) {
            $news_title = $row['news_title'];
            $news_text = $row['news_text'];
            $news_author = $row['news_author'];
            $news_author_id = $row['news_author_id'];
            $news_date = $row['news_date'];
            echo "<h2>$news_title</h2>\n";
            echo "<h3>By $news_author on $news_date</h3>\n";
            echo "<p>$news_text</p>\n";
            echo "<p><a href='./news.php'>Back to main news page.</a></p>\n";
        } else {
            echo "<p class='error'>Can't find a news article with that id ($news_id).</p>\n";
        }
    }

    render_footer();

?>
