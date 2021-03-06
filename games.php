<?php

    include('./includes/functions.php');


    if(!isset($_GET['game_id'])) {
        render_header("Thieves Tavern Games", "");
        if(is_logged_in()) {
            echo "<p class='banner'><a href='./create_game.php'>Create a game</a>?</p>\n";
            $user_id = $_SESSION['user_id'];
        } else {
            $user_id = false;
        }
        echo "<div id='open_games'>\n";
        echo "<h3>Open Games</h3>\n";
        $query = "SELECT games.game_id, games.game_name, games.game_creator, ".
                 "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as players ".
                 "FROM games ".
                 "WHERE games.game_phase=0 AND games.game_password='' AND games.game_id NOT IN ".
                 "(SELECT game_id FROM game_players WHERE user_id='$user_id') ".
                 "LIMIT 20";
        if($rows = mysqli_get_many($query)) {
            echo "<table class='game_table' align='center'>\n";
            echo "<tr class='header'>\n";
            echo "<td class='name'>Name</td>\n";
            echo "<td class='small'>Players</td>\n";
            echo "<td class='small'>Creator</td>\n";
            echo "<td class='small'>Join</td>\n";
            echo "</tr>\n";
            foreach($rows as $row) {
                $game_name = $row['game_name'];
                $game_id = $row['game_id'];
                $game_players = $row['players'];
                $game_creator = get_user_name($row['game_creator']);
                echo "<tr>\n";
                echo "<td class='name'><a href='./games.php?game_id=$game_id'>$game_name</a></td>\n";
                echo "<td>$game_players</td>\n";
                echo "<td>$game_creator</td>\n";
                echo "<td><a href='./games.php?game_id=$game_id&join=true'>Join</a></td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
        } else {
            echo "<p class='error'>No open games.</p>\n";
        }
        echo "</div>\n"; //Close open games
        echo "<div id='my_games'>\n";
        echo "<h3>Your Current Games</h3>\n";
        if(is_logged_in()) {
            //Current games
            echo get_player_open_game_table($user_id);
        } else {
            echo "<p class='error'>Please login to check your games.</p>\n";
        }

        //Finished games
        echo "<div id='finished_games'>\n";
        echo "<h3>Your Finished Games</h3>\n";
        if(is_logged_in()) {
            echo get_player_finished_game_table($user_id);
        } else {
            echo "<p class='error'>Please login to check your games.</p>\n";
        }
        echo "</div>\n";
        echo "</div>\n";
    } else { //Have a game to view
        render_header("Thieves Tavern Games", "");
        $error = "";
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
        } else {
            $user_id = false;
        }
        $game_id = safetify_input($_GET['game_id']);
        if(isset($_REQUEST['join'])) {
            if($user_id) {
                $query = "SELECT game_phase, game_password FROM games WHERE game_id='$game_id'";
                if($row = mysqli_get_one($query)) {
                    $game_password = $row['game_password'];
                    if($row['game_phase'] == 0) {
                        if(count(get_user_open_games($user_id)) <= 4) {
                            $query = "SELECT user_id FROM game_players ".
                                     "WHERE game_id='$game_id' AND user_id='$user_id'";
                            $rows = mysqli_get_many($query);
                            if(count($rows) == 0) {
                                if($game_password != "") {
                                    if(isset($_POST['password'])) {
                                        //Check password
                                    } else {
                                        $error = "This game requires a password, please put it in and try again.";
                                    }
                                } else {
                                    $query = "INSERT INTO game_players(game_id, user_id, role_id) ".
                                             "VALUES('$game_id', '$user_id', 5)";
                                    if(mysqli_insert($query)) {
                                        $channel_id = get_channel_by_name("unassigned_" . $game_id, $game_id);
                                        $query = "INSERT INTO channel_members(user_id, channel_id, channel_post_rights) ".
                                                 "VALUES('$user_id', '$channel_id', '1')";
                                        if(mysqli_insert($query)) {
                                            //Successful
                                            update_game_tracker($game_id);
                                            update_game_players($game_id);
                                        } else {
                                            $error = "Error adding you to the chat channel - $query";
                                        }
                                    } else {
                                        $error = "Error joining game, please try again if its still open.";
                                    }
                                }
                            } else {
                                $error = "You're already playing this game!";
                            }
                        } else {
                            $error = "Sorry, you may only be in 5 games, alive, at once.";
                        }
                    } else {
                        $error = "Sorry, this game is already in progress.";
                    }
                }
            } else {
                echo "<p class='error'>Sorry, you must be <a href='./login.php'>logged in</a> to join a game.</p>";
            }
        }
        if($user_id) {
            if(user_belongs($game_id, $user_id)) {
                $query = "SELECT * ".
                         "FROM games, game_players ".
                         "WHERE games.game_id='$game_id' AND game_players.game_id=games.game_id ".
                         "AND game_players.user_id='$user_id'";
            } else {
                $user_id = false;
                $query = "SELECT * FROM games WHERE game_id='$game_id'";
            }
        } else {
            $query = "SELECT * FROM games WHERE game_id='$game_id'";
        }
        if($row = mysqli_get_one($query)) {
            $game_name = $row['game_name'];
            $game_phase = $row['game_phase'];
            $game_turn = $row['game_turn'];
            $game_creator = $row['game_creator'];
            $player_alive = $row['player_alive'];
            if($user_id) {
                $role_id = $row['role_id'];
            }

            //Make person table
            echo "<div id='player_box_table'>\n";
            echo "</div>\n"; //Close player_box_table

            //Make banner
            if($error != "") {
                echo "<p class='banner'><span class='error'>$error</span></p>\n";
            }
            if($game_phase == 0){
                echo "<div class='banner' id='role_instructions'>";
                echo "Game not yet started.";
                if(is_logged_in() && !$user_id) {
                    echo " Care to <a href='games.php?game_id=".$game_id."&join=true'>join</a>?";
                }
                echo "</div>\n";
            } else if($game_phase == 3) {
                echo "<div class='banner' id='role_instructions'>Game has finished.</div>\n";
            } else {
                if($user_id) {
                    echo "<div class='banner' id='role_instructions'></div>\n";
                } else {
                    echo "<div class='banner' id='role_instructions'>You aren't in this game.</div>\n";
                }
            }
            echo "<div id='action_message'></div>\n";

            //Game information
            echo "<div id='game_information'>\n";
            echo "<h3 class='game_h3'>Game Information</h3>\n";
            echo "<p>\n";
            echo "Creator: ".get_user_name($game_creator)."<br />\n";
            echo "Turn: <span id='game_turn'></span><br />\n";
            echo "Phase: <span id='game_phase'></span><br />\n";
            echo "Target: <span id='target'></span><br />\n";
            echo "Votes to Lynch: <span id='game_vote_to_lynch'></span><br />\n";
            echo "</p>\n";
            echo "<ul id='vote_tally' class='game_player_list'></ul><br />\n";
            echo "Alive: <span id='game_alive'></span>\n";
            echo "<ul class='game_player_list' id='game_alive_list'>\n";
            echo "</ul>\n";
            echo "Dead: <span id='game_dead'></span>\n";
            echo "<ul class='game_player_list' id='game_dead_list'>\n";
            echo "</ul>\n";
            echo "</div>\n";


            //Game chat
            echo "<div id='game_chat'>\n";
            echo "<h3 class='game_h3'>Chat (<span class='chat_channel_name' id='chat_channel'></span>)</h3>\n";
            echo "<div name='chat_text' id='chat_text' >";
            $messages = retrieve_new_messages($user_id, $game_id, 0, true);
            foreach($messages as $message_id=>$message_arr) {
                $date = $message_arr['date'];
                $text = $message_arr['text'];
                $user = $message_arr['user'];
                $channel = $message_arr['channel'];
                echo "<div class='chat_message'>\n";
                echo "<input type='hidden' name='message_id' value='$message_id' />\n";
                echo "<span class='chat_message_channel'><img src='./images/roles/$channel'/></span> \n";
                echo "<span class='chat_message_date'>($date) </span>\n";
                echo "<span class='chat_message_user'>$user</span>: \n";
                echo $text;
                echo "</div>\n";
            }
            echo "</div>\n";
            if(is_logged_in() && user_belongs($game_id, $user_id)) {
                echo "<input type='text' onkeydown='handleKey(event)' name='text_box' id='text_box' style='width: 100%' />\n";
            }
            echo "<input type='hidden' id='user_id' value='$_SESSION[user_id]' />\n";
            echo "<input type='hidden' id='user_hash' value='$_SESSION[user_hash]' />\n";
            echo "<input type='hidden' id='game_id' value='$game_id' />\n";
            echo "</div>\n"; //Close game_chat
        } else {
            //Game doesn't exist
            echo "<p class='error'>Sorry, that game doesn't exist.</p>\n";
        }
    }

    render_footer();

?>
