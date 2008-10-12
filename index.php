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
            $news_author_id = $row['news_author_id'];
            if(strlen($news_text) > 255) {
                $news_text = substr($news_text, 0, 255) . "<a href='./news.php?news_id=$news_id'>...</a>";
            }
            echo "<div id='news_feed_item'>\n";
            echo "<p class='news_title'><a href='./news.php?news_id=$news_id'>$news_title</a></p>\n";
            echo "<p class='news_text'>$news_text</p>\n";
            echo "<span class='news_date'><a href='./profile.php?id=$news_author_id'>$news_author</a> - $news_date</span>\n";
            echo "</div>\n"; //Close news_feed_item
        }
    }
    echo "</div>\n"; //Close news_feed
    echo "<div id='index_content'>\n";
    echo "<h2>Thieves Tavern</h2>\n";
    echo "<p>\n";
    echo "<span class='bolded'>A little about the site - </span>\n";
    echo "Thieves Tavern is a web based version of Mafia, without the need for ".
         "a forum, or moderator. I believe it to be the first incarnation of such a site, though ".
         "will give up that distinction if I'm wrong.";
    echo "</p>\n";
    echo "<p>\n";
    echo "<span class='bolded'>A little about the game - </span>\n";
    echo "Mafia is a great party game in person, and a fun strategic role-playing ".
         "game. It dates back to the 1980's where it was first played in the Psychology ".
         "Department of Moscow State University (as <a href='http://web.archive.org/web/19990302082118/http://members.theglobe.com/mafia_rules/' target='_blank'>".
         "claimed</a> by Dmitri Davidoff). ";
    echo "</p>\n";
    echo "</div>\n"; //Close index content
    render_footer();

?>
