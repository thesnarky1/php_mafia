<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern News");

    if(!isset($_GET['news_id'])) {
        $query = "SELECT * FROM news ORDER BY news_date";
        $result = mysqli_query($dbh, $query);
        while($row = mysqli_fetch_array($result)) {
            $news_id = $row['news_id'];
            $news_text = $row['news_text'];
            $news_date = $row['news_date'];
            $news_title = $row['news_title'];
            echo "<div id='big_news_bulletin'>\n";
            echo "<span class='news_title'>$news_title</span>\n";
            echo "<p>$news_text</p>\n";
            echo "<span class='news_date'>$news_date</span>\n";
            echo "</div>\n";
        }
    }

    render_footer();

?>
