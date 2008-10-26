<?php

    function get_player_open_game_table($user_id) {
        global $phases;
        $to_return = "";
        $query = "SELECT game_players.game_id, games.game_phase, games.game_turn, games.game_name, ".
                 "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as alive, ".
                 "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='N') as dead ".
                 "FROM game_players, games ".
                 "WHERE game_players.user_id='$user_id' AND games.game_id=game_players.game_id ".
                 "AND games.game_phase != 3 ".
                 "ORDER BY games.game_turn DESC";
        if($rows = mysqli_get_many($query)) {
            $to_return .= "<table class='game_table' align='center'>\n";
            $to_return .= "<tr class='header'>\n";
            $to_return .= "<td class='name'>Name</td>\n";
            $to_return .= "<td class='small'>Turn</td>\n";
            $to_return .= "<td class='small'>Phase</td>\n";
            $to_return .= "<td class='small'>Alive</td>\n";
            $to_return .= "<td class='small'>Dead</td>\n";
            $to_return .= "</tr>\n";
            foreach($rows as $row) {
                $game_name = $row['game_name'];
                $game_id = $row['game_id'];
                $game_phase = $row['game_phase'];
                $game_phase = $phases[$game_phase];
                $game_turn = $row['game_turn'];
                $alive = $row['alive'];
                $dead = $row['dead'];
                $to_return .= "<tr>\n";
                $to_return .= "<td class='name'><a href='./games.php?game_id=$game_id'>$game_name</a></td>\n";
                $to_return .= "<td>$game_turn</td>\n";
                $to_return .= "<td>$game_phase</td>\n";
                $to_return .= "<td>$alive</td>\n";
                $to_return .= "<td>$dead</td>\n";
                $to_return .= "</tr>\n";
            }
            $to_return .= "</table>\n";
        } else {
            $to_return .= "<p class='error'>" . get_user_name($user_id) . " is not currently in any games.</p>\n";
        }
        return $to_return;
    }

    function get_player_finished_game_table($user_id) {
        global $phases;
        $to_return = "";
        $query = "SELECT game_players.game_id, games.game_phase, games.game_turn, games.game_name ".
                 "FROM game_players, games ".
                 "WHERE game_players.user_id='$user_id' AND games.game_id=game_players.game_id ".
                 "AND games.game_phase='3' ".
                 "ORDER BY games.game_turn DESC";
        if($rows = mysqli_get_many($query)) {
            $to_return .= "<table class='game_table' align='center'>\n";
            $to_return .= "<tr class='header'>\n";
            $to_return .= "<td class='name'>Name</td>\n";
            $to_return .= "<td class='small'>Turn</td>\n";
            $to_return .= "<td class='small'>Phase</td>\n";
            $to_return .= "</tr>\n";
            foreach($rows as $row) {
                $game_name = $row['game_name'];
                $game_id = $row['game_id'];
                $game_phase = $row['game_phase'];
                $game_phase = $phases[$game_phase];
                $game_turn = $row['game_turn'];
                $to_return .= "<tr>\n";
                $to_return .= "<td class='name'><a href='./games.php?game_id=$game_id'>$game_name</a></td>\n";
                $to_return .= "<td>$game_turn</td>\n";
                $to_return .= "<td>$game_phase</td>\n";
                $to_return .= "</tr>\n";
            }
            $to_return .= "</table>\n";
        } else {
            $to_return .= "<p class='error'>" . get_user_name($user_id) . " is not currently in any games.</p>\n";
        }
        return $to_return;
    }
?>
