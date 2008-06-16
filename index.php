<?php

    include('./includes/functions.php');
    
    render_header();

    echo "<div id='news_feed'>\n";
    echo "<h3><a href='./news.php'>News</a></h3>\n";
    $query = "SELECT * FROM news ORDER BY news_date DESC LIMIT 10";
    $result = mysqli_query($dbh, $query);
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result)) {
            $news_id = $row['news_id'];
            $news_text = $row['news_text'];
            $news_date = $row['news_date'];
            $news_title = $row['news_title'];
            $news_author = $row['news_author'];
            if(strlen($news_text) > 255) {
                $news_text = substr($news_text, 0, 255) . "<a href='./news.php?news_id=$news_id'>...</a>";
            }
            echo "<div id='news_feed_item'>\n";
            echo "<p class='news_title'><a href='./news.php?news_id='$news_id'>$news_title</a></p>\n";
            echo "<p class='news_text'>$news_text</p>\n";
            echo "<span class='news_date'>$news_author - $news_date</span>\n";
            echo "</div>\n"; //Close news_feed_item
        }
    }
    echo "</div>\n"; //Close news_feed
    

    render_footer();

?>
