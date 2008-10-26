<?php

    function get_user_open_games($user_id) {
        $query = "SELECT games.game_id ".
                 "FROM games, game_players ".
                 "WHERE game_players.user_id='$user_id' AND ".
                 "games.game_id=game_players.game_id AND ".
                 "games.game_phase IS NOT '3' AND ".
                 "game_players.player_alive='Y'";
        $rows = mysqli_get_many($query);
        return $rows;
    }

    function get_game_phase_turn($game_id) {
        $query = "SELECT game_phase, game_turn ".
                 "FROM games ".
                 "WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            return $row;
        }
        return false;
    }

    function user_belongs($game_id, $user_id) {
        global $dbh;
        $query = "SELECT game_id FROM game_players WHERE game_id='$game_id' AND user_id='$user_id'";
        if(mysqli_get_one($query)) {
            return true;
        } else {
            return false;
        }
    }

    function get_investigated_users($game_id, $user_id) {
        global $dbh;
        $to_return = array();
        $query = "SELECT target_id FROM game_investigations ".
                 "WHERE game_id='$game_id' AND user_id='$user_id'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $to_return[] = $row['target_id'];
            }
        }
        return $to_return;
    }

    function get_action_priority($game_id, $user_id) {
        $query = "SELECT roles.role_action_priority ".
                 "FROM roles, game_players ".
                 "WHERE game_players.game_id='$game_id' AND game_players.user_id='$user_id' AND ".
                 "roles.role_id=game_players.role_id";
        if($row = mysqli_get_one($query)) {
            return $row['role_action_priority'];
        }
    }

    function auto_ready_game($game_id) {
        $nothing = get_action_by_enum("NO_ACTION");
        $query = "SELECT user_id ".
                 "FROM game_players ".
                 "WHERE game_id='$game_id' AND player_alive='Y'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $user_id = $row['user_id'];
                $actions = get_user_actions($game_id, $user_id);
                if(in_array($nothing, $actions) || count($actions) == 0) {
                    set_player_ready($game_id, $user_id, true);
                } else {
                }
            }
        }
    }

    function carry_out_actions($game_id) {
        global $dbh;
        $query = "SELECT game_phase, game_turn FROM games WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            $game_turn = $row['game_turn'];
            $game_phase = $row['game_phase'];
            $query = "SELECT user_id, target_id, action_id FROM game_actions ".
                     "WHERE game_id='$game_id' AND game_turn='$game_turn' AND ".
                     "game_phase='$game_phase' ORDER BY game_action_priority DESC";
            if($rows = mysqli_get_many($query)) {
                $to_kill = array(); //(user_id=>(target_id, true))
                $to_save = array(); //(target_id)
                $to_investigate = array(); //(user_id=>target_id)
                $to_lynch = array(); //target_id=>votes);
                foreach($rows as $row) {
                    $user_id = $row['user_id'];
                    $target_id = $row['target_id'];
                    $action_id = $row['action_id'];
                    $action_enum = get_action_by_id($action_id);
                    switch ($action_enum) {
                        case "KILL":
                            if($target_id != 0) {
                                $to_kill[$user_id] = $target_id;
                            }
                            break;
                        case "SAVE":
                            if($target_id != 0) {
                                $to_save[] = $target_id;
                                add_message(get_channel_by_name("doctor_$user_id_$game_id", $game_id),
                                            get_system_id(),
                                            "Being the selfish doctor you are, you elect to let everyone die tonight.");
                            } else {
                                add_message(get_channel_by_name("doctor_$user_id_$game_id", $game_id),
                                            get_system_id(),
                                            "You bring " . get_user_name($target_id) . 
                                            " into the OR, praying its not too late to save them.");
                            }
                            break;
                        case "INVESTIGATE":
                            if($target_id != 0) {
                                $to_investigate[$user_id] = $target_id;
                            } else {
                                add_message(get_channel_by_name("cop_$user_id_$game_id", $game_id),
                                            get_system_id(),
                                            "You decide to stay at home tonight, rather than investigate the rash of murders.");
                            }
                            break;
                        case "LYNCH":
                        case "NO_LYNCH":
                            if(!isset($to_lynch[$target_id])) {
                                $to_lynch[$target_id] = 0;
                            }
                            $to_lynch[$target_id]++;
                            break;
                    }
                }
                $already_dead = array();
                if($game_phase == 2) { //Day actions (lynch only, so far)
                    $vote_to_lynch = get_votes_needed($game_id);
                    foreach($to_lynch as $lynchee=>$vote) {
                        if($vote >= $vote_to_lynch) {
                            //Kill player
                            if($lynchee == 0) { //No lynch vote
                                add_message(get_system_channel($game_id),
                                            get_system_id(),
                                            "There isn't enough proof to convict anyone, no lynch this turn.");
                            } else {
                                if(!in_array($lynchee, $already_dead)) {
                                    $victim = get_user_name($lynchee);
                                    kill_player($lynchee, $game_id);
                                    add_message(get_system_channel($game_id),
                                                get_system_id(),
                                                "$victim is lynched.");
                                    $already_dead[] = $lynchee;
                                    if($winners = can_game_end($game_id)) {
                                        end_game($game_id, $winners);
                                        die();
                                    }
                                }
                            }
                        }
                    }
                } else { //Night actions (Kill, save, investigate)
                    //Investigation stuff comes first
                    foreach($to_investigate as $user_id=>$target_id) {
                        $chan_name = "cop_" . $user_id . "_" . $game_id;
                        $query = "SELECT roles.role_id, factions.faction_name ".
                                 "FROM roles, game_players, factions ".
                                 "WHERE game_players.game_id='$game_id' AND ".
                                 "game_players.user_id='$target_id' AND ".
                                 "roles.role_id=game_players.role_id AND ".
                                 "factions.faction_id=roles.investigate_faction_id";
                        if($row = mysqli_get_one($query)) {
                            $target_role_name = $row['faction_name'];
                            $target_role_id = $row['role_id'];
                            $query = "INSERT into game_investigations(game_id, user_id, target_id, role_id) ".
                                     "VALUES('$game_id', '$user_id', '$target_id', '$target_role_id')";
                            if(mysqli_insert($query)) {
                                add_message(get_channel_by_name($chan_name, $game_id),
                                            get_system_id(),
                                            "You investigate " . get_user_name($target_id) . 
                                            " and discover their faction is: $target_role_name.");
                            }
                        }
                    }
                    //Kill stuff
                    foreach($to_kill as $killer_id=>$killee_id) {
                        if(in_array($killee_id, $to_save)) { //If the guy was saved, don't allow him to be killed
                            $to_kill[$killer_id][1] = false;
                        } else {
                            if(!in_array($killee_id, $already_dead)) {
                                //Kill player
                                kill_player($killee_id, $game_id);
                                add_message(get_system_channel($game_id),
                                            get_system_id(),
                                            "Tragically, " . get_user_name($killee_id) . " was found dead during the night.");
                                $already_dead[] = $killee_id;
                                if($winners = can_game_end($game_id)) {
                                    end_game($game_id, $winners);
                                    die();
                                }
                            }
                        }
                    } 
                }
            }
        }
    }

    function lock_game($game_id, $lock) {
        global $dbh;
        $query = "UPDATE games SET game_locked='";
        if($lock) {
            $query .= "1";
        } else {
            $query .= "0";
        }
        $query .= "' WHERE game_id='$game_id'";
        mysqli_set_one($query);
    }

    function is_game_locked($game_id) {
        global $dbh;
        $to_return = true;
        $query = "SELECT game_locked FROM games WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            if($row['game_locked'] == 0) {
                $to_return = false;
            }
        }
        return $to_return;
    }

    function can_phase_change($game_id) {
        global $dbh;
        $game_phase_turn = get_game_phase_turn($game_id);
        if($game_phase_turn) {
            $game_phase = $game_phase_turn['game_phase'];
            $game_turn = $game_phase_turn['game_turn'];
            if($game_phase == 1) { //Night
                $query = "SELECT DISTINCT player_ready FROM game_players WHERE game_id='$game_id' AND player_alive='Y'";
                $rows = mysqli_get_many($query);
                if(count($rows) == 1) {
                    $row = $rows[0];
                    if($row['player_ready'] == 'Y') {
                        //All players ready
                        $query = "SELECT DISTINCT roles.role_target_group ".
                                 "FROM roles, game_players ".
                                 "WHERE game_players.game_id='$game_id' AND ".
                                 "roles.role_id=game_players.role_id AND ".
                                 "roles.role_target_group IS NOT NULL";
                        $rows = mysqli_get_many($query);
                        if(count($rows) == 0) {
                            //Good to go
                            return true;
                        } else {
                            foreach($rows as $row) {
                                $role_target_group = $row['role_target_group'];
                                $target_query = "SELECT DISTINCT game_actions.target_id ".
                                         "FROM game_players, roles, game_actions ".
                                         "WHERE game_players.game_id='$game_id' AND ".
                                         "roles.role_target_group='$role_target_group' AND ".
                                         "roles.role_id=game_players.role_id AND ".
                                         "game_actions.user_id=game_players.user_id AND ".
                                         "game_actions.game_turn='$game_turn' AND ".
                                         "game_actions.game_phase='$game_phase' AND ".
                                         "game_actions.game_id=game_players.game_id ";
                                $target_rows = mysqli_get_many($target_query);
                                if($target_rows && (count($target_rows) == 1 || count($target_rows) == 0)) {
                                    //echo "All $role_target_group want to target the same.\n";
                                    //Have an agreed upon target
                                    return true;
                                } else {
                                    //echo " The $role_target_group can't decide. $target_query\n";
                                    return false;
                                }
                            }
                        }
                    }
                }
            } else if($game_phase == 2) { //Day
                $votes_required = get_votes_needed($game_id);
                $lynch_action = get_action_by_enum("LYNCH");
                $no_lynch_action = get_action_by_enum("NO_LYNCH");
                $query = "SELECT target_id FROM game_actions ".
                         "WHERE game_id='$game_id' AND game_phase='$game_phase' AND ".
                         "game_turn='$game_turn' AND ".
                         "(action_id='$lynch_action' OR action_id='$no_lynch_action')";
                $rows = mysqli_get_many($query);
                if($rows && count($rows) >= $votes_required) {
                    $lynchees = array();
                    foreach($rows as $row) {
                        $target_id = $row['target_id'];
                        if(!isset($lynchees[$target_id])) {
                            $lynchees[$target_id] = 0;
                        }
                        $lynchees[$target_id]++;
                    }
                    foreach($lynchees as $lynchee=>$votes) {
                        if($votes >= $votes_required) {
                            $to_return = true;
                        }
                    }
                }
            }
        }
        return $to_return;
    }

    function get_votes_needed($game_id) {
        global $dbh;
        $votes_needed = false;
        $query = "SELECT COUNT(user_id) as cnt FROM game_players ".
                 "WHERE game_id='$game_id' AND player_alive='Y'";
        if($row = mysqli_get_one($query)) {
            $total_alive = $row['cnt'];
            $votes_needed = floor(($total_alive / 2) + 1);
        }
        return $votes_needed;
    }

    function clear_player_action($game_id, $player_id) {
        global $dbh;
        $game_phase_turn = get_game_phase_turn($game_id);
        if($game_phase_turn) {
            $game_turn = $game_phase_turn['game_turn'];
            $game_phase = $game_phase_turn['game_phase'];
            $query = "DELETE FROM game_actions WHERE game_id='$game_id' AND user_id='$user_id' ".
                     "AND game_turn='$game_turn' AND game_phase='$game_phase'";
            mysqli_delete($query);
        }
    }

    function get_user_name($user_id) {
        global $dbh;
        if($user_id == 0) {
            return "no one";
        } else {
            $query = "SELECT user_name FROM users WHERE user_id='$user_id'";
            if($row = mysqli_get_one($query)) {
                return $row['user_name'];
            }
        }
        return false;
    }

    function add_player_action($game_id, $user_id, $action_id, $target_id, $priority=0) {
        $game_phase_turn = get_game_phase_turn($game_id);
        if($game_phase_turn) {
            //We have a valid game
            $game_turn = $game_phase_turn['game_turn'];
            $game_phase = $game_phase_turn['game_phase'];
            $query = "SELECT game_action_id FROM game_actions ".
                     "WHERE game_id='$game_id' AND user_id='$user_id' AND ".
                     "game_phase='$game_phase' AND game_turn='$game_turn'";
            if($row = mysqli_get_one($query)) {
                //Update existing
                $game_action_id = $row['game_action_id'];
                $query = "UPDATE game_actions SET action_id='$action_id', game_action_priority='$priority', ".
                         "target_id='$target_id' WHERE game_action_id='$game_action_id'";
                mysqli_set_one($query);
            } else {
                //Insert new action
                $query = "INSERT INTO game_actions(game_id, user_id, action_id, game_turn, game_phase, target_id, game_action_priority) ".
                         "VALUES('$game_id', '$user_id', '$action_id', '$game_turn', '$game_phase', '$target_id', '$priority')";
                mysqli_insert($query);
            }
        }


    }

    function start_game($game_id) {
        lock_game($game_id, true);
        dole_out_roles($game_id);
        initialize_channels($game_id);
        next_phase($game_id);
        lock_game($game_id, false);
    }

    function update_game_recent_date($game_id) {
        $query = "UPDATE games SET game_recent_date=NOW() WHERE game_id='$game_id'";
        $result = mysqli_set_one($query);
    }

    function dole_out_roles($game_id) {
        global $dbh;
        $query = "SELECT user_id FROM game_players WHERE game_id='$game_id'";
        if($rows = mysqli_get_many($query)) {
            $num_players = count($rows);
            $players = array();
            $rand_players = array();
            $finished_players = array();
            foreach($rows as $row) {
                $players[] = $row['user_id'];
            }
            $query = "SELECT roleset_roles, roleset_id ".
                     "FROM rolesets ".
                     "WHERE roleset_num_players='$num_players' ".
                     "ORDER BY RAND() LIMIT 1";
            if($row = mysqli_get_one($query)) {
                $roleset = explode(",", $row['roleset_roles']);
                $roleset_id = $row['roleset_id'];
                $roleset_length = count($roleset);
                $rand_indices = array_rand($players, $num_players);
                foreach($rand_indices as $rand_index) {
                    $rand_players[] = $players[$rand_index];
                }
                foreach($rand_players as $rand_player) {
                    if($role = array_pop($roleset)) { //If we have special roles, fill them first
                        $finished_players[$rand_player] = $role;
                    } else {
                        $finished_players[$rand_player] = 1; //Make them a townie
                    }
                }
                //Now update game_players to reflect these roles
                foreach($finished_players as $finished_player=>$finished_role) {
                    $query = "UPDATE game_players SET role_id='$finished_role', ".
                             "player_ready='N', player_needs_update='1' ".
                             "WHERE game_id='$game_id' AND user_id='$finished_player'";
                    if(mysqli_set_one($query)) {
                        $query = "UPDATE games SET game_roleset_id='$roleset_id' WHERE game_id='$game_id'";
                        mysqli_set_one($query);
                    } else {
                        echo "DB error - $query";
                    }
                }
            } else { //Pulling random row failed
            }
        } else { //Getting user_ids failed
        }
    }

    function can_start_game($game_id) {
        global $dbh;
        $unready_players = false;
        $query = "SELECT users.user_name FROM game_players, users ".
                 "WHERE game_players.game_id='$game_id' ".
                 "AND game_players.player_ready='N' ".
                 "AND users.user_id=game_players.user_id";
        $rows = mysqli_get_many($query);
        if(count($rows) > 0) {
            $unready_players = array();
            foreach($rows as $row) {
                $unready_players[] = $row['user_name'];
            }
            echo "Game cannot start because someone's not ready: " . implode(", ", $unready_players);
            return false;
        } else {
            $query = "SELECT user_id FROM game_players WHERE game_id='$game_id'";
            if($rows = mysqli_get_many($query)) {
                $num_players = count($rows);
                $query = "SELECT roleset_roles FROM rolesets WHERE roleset_num_players='$num_players'";
                if($rows = mysqli_get_many($query)) {
                    return true;
                } else {
                    echo "Game cannot start with that number of players($num_players), sorry... we just don't know of any fair rolesets.";
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    function set_player_ready($game_id, $user_id, $ready) {
        global $dbh;
        $query = "UPDATE game_players SET player_ready='";
        if($ready) {
            $query .= "Y";
        } else {
            $query .= "N";
        }
        $query .= "' WHERE game_id='$game_id' AND user_id='$user_id'";
        mysqli_set_one($query);
    }

    function get_action_by_id($action_id) {
        global $dbh;
        $query = "SELECT action_enum FROM actions WHERE action_id='$action_id'";
        if($row = mysqli_get_one($query)) {
            return $row['action_enum'];
        } else {
            return false;
        }
    }

    function get_action_by_enum($action_enum) {
        global $dbh;
        $query = "SELECT action_id FROM actions WHERE action_enum='$action_enum'";
        if($row = mysqli_get_one($query)) {
            return $row['action_id'];
        } else {
            return false;
        }
    }

    function get_user_actions($game_id, $user_id) {
        global $dbh;
        $to_return = array();
        $no_action = get_action_by_enum("NO_ACTION");
        $query = "SELECT game_players.player_ready, game_players.role_id, ".
                 "game_players.player_alive, games.game_phase, games.game_creator ".
                 "FROM game_players, games ".
                 "WHERE games.game_id='$game_id' AND game_players.user_id='$user_id' ".
                 "AND game_players.game_id=games.game_id";
        if($row = mysqli_get_one($query)) {
            $player_ready = $row['player_ready'];
            $player_alive = $row['player_alive'];
            $game_phase = $row['game_phase'];
            $game_creator = $row['game_creator'];
            $role_id = $row['role_id'];
            if($player_alive == 'Y') {
                if($game_phase == 2 || $game_phase == 0) { //day
                    $query = "SELECT day_action_id, day_alt_action_id FROM roles WHERE role_id='$role_id'";
                    if($row = mysqli_get_one($query)) {
                        $to_return[] = $row['day_action_id'];
                        $to_return[] = $row['day_alt_action_id'];
                    }
                } else {
                    $query = "SELECT night_action_id, night_alt_action_id FROM roles WHERE role_id='$role_id'";
                    if($row = mysqli_get_one($query)) {
                        $to_return[] = $row['night_action_id'];
                        $to_return[] = $row['night_alt_action_id'];
                    }
                }
                if($player_ready == 'Y' && !in_array($no_action, $to_return)) {
                    $to_return[] = get_action_by_enum("UN_READY");
                    if($user_id == $game_creator) {
                        $to_return[] = get_action_by_enum("START");
                    }
                }
            }
        }
        return $to_return;
    }

    function end_game($game_id, $winning_faction) {
        global $dbh;
        $faction_id = get_faction_id($winning_faction);
        $winning_roles = get_roles_by_faction_id($faction_id);
        //Set game phase to 3
        //lock game
        //Kill off ability to chat in game channels
        $query = "SELECT channel_id FROM channels WHERE game_id='$game_id'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                revoke_channel($row['channel_id']);
            }
        }
        $query = "UPDATE games SET game_phase='3', game_locked='1' WHERE game_id='$game_id'";
        $result = mysqli_query($dbh, $query);
        //Send some spam saying its over, of course
        add_message(get_system_channel($game_id),
                    get_system_id(),
                    "With much bloodshed, the game ends. The winning faction was: $winning_faction.");
        //Add to stats table (player, win|loss, role)
        $query = "SELECT user_id, role_id, player_alive ".
                 "FROM game_players ".
                 "WHERE game_id='$game_id'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $player_alive = $row['player_alive'];
                $user_id = $row['user_id'];
                $role_id = $row['role_id'];
                if($player_alive == 'Y') {
                    if(in_array($role_id, $winning_roles)) {
                        $result_id = get_result_by_enum("LIVE_WIN");
                    } else {
                        $result_id = get_result_by_enum("LIVE_LOSS");
                    }
                } else {
                    if(in_array($role_id, $winning_roles)) {
                        $result_id = get_result_by_enum("DEAD_WIN");
                    } else {
                        $result_id = get_result_by_enum("DEAD_LOSS");
                    }
                }
                $query = "INSERT INTO game_player_results(game_id, user_id, role_id, result_id) ".
                          "VALUES('$game_id', '$user_id', '$role_id', '$result_id')";
                mysqli_insert($query);
            }
        }

        //Add stats to game_results table
        $query = "SELECT game_roleset_id ".
                 "FROM games ".
                 "WHERE game_id='$game_id";
        if($row = mysqli_get_one($query)) {
            $roleset_id = $row['game_roleset_id'];
            $query = "INSERT INTO game_results(game_id, roleset_id, faction_id) ".
                     "VALUES('$game_id', '$roleset_id', '$faction_id') ";
            mysqli_insert($query);
        }

        update_game_players($game_id);
        update_game_tracker($game_id);
    }

    function get_result_by_enum($enum) {
        global $dbh;
        $query = "SELECT result_id FROM results WHERE result_enum='$enum'";
        if($row = mysqli_get_one($query)) {
            return $row['result_id'];
        } else {
            return false;
        }
    }

    function can_game_end($game_id) {
        global $dbh;
        $roles = array();
        $query = "SELECT factions.faction_name, game_players.player_alive ".
                 "FROM game_players, roles, factions ".
                 "WHERE game_players.game_id='$game_id' AND roles.role_id=game_players.role_id ".
                 "AND factions.faction_id=roles.faction_id";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $role_faction = $row['faction_name'];
                $player_alive = $row['player_alive'];
                if($player_alive == 'Y') {
                    if(!isset($roles[$role_faction])) {
                        $roles[$role_faction] = 0;
                    }
                    $roles[$role_faction]++;
                }
            }
            $over = false;

            if(isset($roles['Town']) && count($roles) == 1) {
                return "Town";
            } else if((!isset($roles['Town']) || $roles['Town'] == 1) && isset($roles['Antitown']) &&
                       !isset($roles['Psychopaths'])) {
                return "Antitown";
            } else if((!isset($roles['Town']) || $roles['Town'] == 1) && !isset($roles['Anittown']) &&
                        $roles['Psychopaths'] == 1) {
                return "Psychopaths";
            }
        }
        return false;
    }

    function get_faction_id($faction) {
        $query = "SELECT faction_id FROM factions WHERE faction_name LIKE '$faction'";
        if($row = mysqli_get_one($query)) {
            return $row['faction_id'];
        } else {
            return false;
        }
    }

    function get_roles_by_faction_id($faction) {
        $role_ids = array();
        $query = "SELECT role_id FROM roles WHERE faction_id='$faction'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                $role_ids[] = $row['role_id'];
            }
        }
        return $role_ids;
    }

    function capitalize($str) {
        $str = strtoupper(substr($str, 0, 1)) . 
               substr($str, 1);
        return $str;
    }

    function next_phase($game_id) {
        $system_id = get_system_id();
        $chan_id = get_system_channel($game_id);
        $query = "SELECT game_turn, game_phase, game_locked FROM games WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            $game_turn = $row['game_turn'];
            $game_phase = $row['game_phase'];
            $game_locked = $row['game_locked'];
            if($game_locked == 1) {
                if($game_phase == 1) {
                    //Just update game_phase
                    $game_phase++;
                    $query = "UPDATE games ".
                             "SET game_phase='$game_phase', game_recent_date=NOW() ".
                             "WHERE game_id='$game_id'";
                    if(mysqli_set_one($query)) {
                        if($chan_id) {
                            add_message($chan_id, $system_id, "Another day breaks over the town.");
                        }
                    }
                } else {
                    //Increment turn as well.
                    if($game_phase != 0) {
                        $game_phase--;
                    } else {
                        $game_phase++;
                    }
                    $game_turn++;
                    $query = "UPDATE games ".
                             "SET game_phase='$game_phase', game_turn='$game_turn', game_recent_date=NOW() ".
                             "WHERE game_id='$game_id'";
                    if(mysqli_set_one($query)) {
                        if($chan_id) {
                            add_message($chan_id, $system_id, "Night creeps silently over the town as turn $game_turn begins.");
                        } else {
                            echo "Can't find system channel";
                        }
                    }
                }
                update_players_ready($game_id);
                auto_ready_game($game_id);
                update_game_players($game_id);
                update_game_tracker($game_id);
            }
        } 
    }

    function update_players_ready($game_id) {
        global $dbh;
        $query = "UPDATE game_players SET player_ready='N' WHERE game_id='$game_id'";
        mysqli_set_many($query);
    }

    function update_game_players($game_id) {
        global $dbh;
        $query = "SELECT user_id FROM game_players WHERE game_id='$game_id'";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
                update_player_needs_update($game_id, $row['user_id'], true); 
            }
        }
    }

    function update_player_needs_update($game_id, $user_id, $needs) {
        update_game_tracker($game_id);
//        $query = "UPDATE game_players SET player_needs_update='";
//        if($needs) {
//            $query .= "1";
//        } else {
//            $query .= "0";
//        }
//        $query .= "' WHERE game_id='$game_id' AND user_id='$user_id'";
//        mysqli_set_one($query);
    }

    function update_game_tracker($game_id) {
        global $dbh;
        $query = "SELECT game_tracker FROM games WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            $tracking_num = $row['game_tracker'];
            $tracking_num += rand(1, 23);
            $query = "UPDATE games ".
                     "SET game_tracker='$tracking_num' ".
                     "WHERE game_id='$game_id'";
            mysqli_set_one($query);
        }
    }

    function player_needs_update_id($game_id, $user_id) {
        global $dbh;
        $query = "SELECT player_needs_update FROM game_players ".
                 "WHERE game_id='$game_id' AND user_id='$user_id'";
        if($row = mysqli_get_one($query)) {
            if($row['player_needs_update'] == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        return false;
    }

    function player_needs_update_tracker($game_id, $tracker_id) {
        global $dbh;
        $query = "SELECT game_tracker FROM games WHERE game_id='$game_id'";
        if($row = mysqli_get_one($query)) {
            if($row['game_tracker'] < $tracker_id) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    function get_user_role_faction($game_id, $user_id) {
        global $dbh;
        $to_return = array();
        $query = "SELECT roles.role_id, factions.faction_name ".
                 "FROM roles, game_players, factions ".
                 "WHERE game_players.game_id='$game_id' AND ".
                 "game_players.user_id='$user_id' AND ".
                 "roles.role_id=game_players.role_id AND ".
                 "factions.faction_id=roles.faction_id";
        if($row = mysqli_get_one($query)) {
            $to_return['role'] = $row['role_id'];
            $to_return['faction'] = $row['faction_name'];
            return $to_return;
        }
        return false;
    }

    function get_game_information($game_id, $old_game_tracker, $force, $user_id=0) {
        global $dbh, $phases;
        $needs_update = false;
        $user_role_faction = false;
        if($user_id == 0) {
            $user_belongs = false;
        } else {
            if(user_belongs($game_id, $user_id)) {
                $user_belongs = true;
            } else {
                $user_belongs = false;
            }
        }
        $to_return = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $to_return .= "<game_data>\n";
        if(false) {                                 //if($user_belongs) {
            //Track based on player_needs_update
            $needs_update = player_needs_update_id($game_id, $user_id);
        } else {
            //Track based on game_tracker
            $needs_update = player_needs_update_tracker($game_id, $old_game_tracker);
        }
        if($force) { //ignore anything else, we HAVE to update
            $needs_update = true;
        }
        if($needs_update) {
            if($user_belongs) {
                update_player_needs_update($game_id, $user_id, false); //Turn off needing an update... we just gave it
                $user_role_faction = get_user_role_faction($game_id, $user_id);
            }
            $query = "SELECT * FROM games WHERE game_id='$game_id'";
            if($row = mysqli_get_one($query)) {
                $game_phase = $row['game_phase'];
                $game_creator = $row['game_creator'];
                $game_turn = $row['game_turn'];
                $game_tracker = $row['game_tracker'];
                $banner = false;
                $alt_banner = false;
                $role_instructions = "";
                $to_return .= "<turn>$game_turn</turn>\n";
                $to_return .= "<phase>$phases[$game_phase]</phase>\n";
                $to_return .= "<tracker>$game_tracker</tracker>\n";
                $to_return .= "<votes_required>" . get_votes_needed($game_id) . "</votes_required>\n";
                if($user_belongs) {
                    $investigated_peeps = get_investigated_users($game_id, $user_id);
                } else {
                    $investigated_peeps = array();
                }
                if($game_phase == 2) { //If its day, lets give a vote tally
                    $lynch_action = get_action_by_enum("LYNCH");
                    $no_lynch_action = get_action_by_enum("NO_LYNCH");
                    $query = "SELECT COUNT(*) as cnt, target_id ".
                             "FROM game_actions ".
                             "WHERE game_id='$game_id' AND game_phase='$game_phase' AND ".
                             "game_turn='$game_turn' AND (action_id='$lynch_action' OR ".
                             "action_id='$no_lynch_action') ".
                             "GROUP BY target_id ORDER BY cnt DESC";
                    if($rows = mysqli_get_many($query)) {
                        foreach($rows as $row) {
                            $votes = $row['cnt'];
                            $target_id = $row['target_id'];
                            if($target_id == 0) {
                                $target_name = "Lynch no one";
                            } else {
                                $target_name = get_user_name($target_id);
                            }
                            $to_return .= "<vote_tally>";
                            $to_return .= "<name>$target_name</name>\n";
                            $to_return .= "<vote>$votes votes</vote>\n";
                            $to_return .= "</vote_tally>\n";
                        }
                    }
                } else if($game_phase == 0) { //Return a ready list
                    $query = "SELECT user_id, player_ready ".
                             "FROM game_players ".
                             "WHERE game_id='$game_id' ".
                             "ORDER BY player_ready ASC";
                    if($rows = mysqli_get_many($query)) {
                        foreach($rows as $row) {
                            $name = get_user_name($row['user_id']);
                            if($row['player_ready'] == 'Y') {
                                $ready = "Ready";
                            } else {
                                $ready = "NOT Ready";
                            }
                            $to_return .= "<vote_tally>\n";
                            $to_return .= "<name>$name</name>\n";
                            $to_return .= "<vote>$ready</vote>\n";
                            $to_return .= "</vote_tally>\n";
                        }
                    }
                }
                $to_return .= "<player_list>\n";
                $query = "SELECT users.user_name, users.user_avatar, ".
                         "game_players.player_alive, game_players.player_ready, ".
                         "users.user_id, ".
                         "roles.role_name, roles.role_channel, ".
                         "factions.faction_name, roles.role_id, ".
                         "roles.day_instructions, roles.night_instructions, ".
                         "roles.role_inform_others ".
                         "FROM users, game_players, roles, factions ".
                         "WHERE game_players.game_id='$game_id' AND ".
                         "roles.role_id=game_players.role_id AND ".
                         "users.user_id=game_players.user_id AND ".
                         "factions.faction_id=roles.faction_id ".
                         "ORDER BY game_players.player_alive DESC ";
                if($rows = mysqli_get_many($query)) {
                    $real_channel = false;
                    foreach($rows as $row) {
                        $channel = $row['role_channel'];
                        $player_id = $row['user_id'];
                        $user_name = $row['user_name'];
                        $user_avatar = $row['user_avatar'];
                        $player_alive = $row['player_alive'];
                        $player_ready = $row['player_ready'];
                        $role_name = $row['role_name'];
                        $role_faction = $row['faction_name'];
                        $role_id = $row['role_id'];
                        $role_inform_others = $row['role_inform_others'];
                        if($game_phase == 1) {
                            $role_instructions = $row['night_instructions'];
                        } else {
                            $role_instructions = $row['day_instructions'];
                        }
                        $to_return .= "<player>\n";
                        $to_return .= "<id>$player_id</id>\n";
                        $to_return .= "<name>$user_name</name>\n";
                        $to_return .= "<avatar>$user_avatar</avatar>\n";
                        $to_return .= "<alive>$player_alive</alive>\n";
                        if($player_id == $user_id || $game_phase == 3) {
                            $to_return .= "<role_name>$role_name</role_name>\n";
                            $to_return .= "<role_faction>$role_faction</role_faction>\n";
                            if($player_id == $user_id) {
                                $to_return .= "<role_instructions>";
                                $to_return .= "Your role is: $role_name. ";
                                if($player_alive == 'Y') {
                                    $to_return .= $role_instructions;
                                } else {
                                    $to_return .= "Too bad you are also dead.";
                                }
                            $to_return .= "</role_instructions>\n";
                            }
                        } else if($role_inform_others == 1 && $role_id == $user_role_faction['role']) {
                            $to_return .= "<role_faction>$role_faction</role_faction>\n";
                            $to_return .= "<role_name>$role_name</role_name>\n";
                        } else if(in_array($player_id, $investigated_peeps)) {
                            if($role_faction == "Psychopaths") {
                                $role_faction = "Antitown";
                            }
                            $to_return .= "<role_faction>$role_faction</role_faction>\n";
                        }
    
                        //Anything specific to the player viewing needs to go after this.
                        if($player_id == $user_id) {
    
                            //Get channel name
                            if($game_phase == 1) { //Daytime
                                if($channel && $channel != "") {
                                    if(false !== strpos($channel, "_")) {
                                        $channel = substr($channel, 0, strpos($channel, "_"));
                                    }
                                    $real_channel = capitalize($channel);
                                } else {
                                    $real_channel = "No";
                                }
                            } else {
                                $real_channel = "Town";
                            }
                            
                            //If alive, we want to put up the banner for actions not targetting people
                            if($player_alive == "Y") {
    
                                if($game_phase == 2 || $game_phase == 0) { //If daytime
                                    $query = "SELECT * FROM ".
                                             "(SELECT actions.action_id as day_action_id, ".
                                             "actions.action_banner as day_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.day_action_id AND ".
                                             "roles.role_id='$role_id') as day_actions, ".
                                             "(SELECT actions.action_id as day_alt_action_id, ".
                                             "actions.action_banner as day_alt_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.day_alt_action_id AND ".
                                             "roles.role_id='$role_id') as day_alt_actions";
                                    if($row2 = mysqli_get_one($query)) {
                                        $action_id = $row2['day_action_id'];
                                        $banner_action = $row2['day_alt_action_id'];
                                        $banner = $row2['day_alt_action_banner'];
                                    }
                                } else { //If nighttime
                                    $query = "SELECT * FROM ".
                                             "(SELECT actions.action_id as night_action_id, ".
                                             "actions.action_banner as night_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.night_action_id AND ".
                                             "roles.role_id='$role_id') as night_actions, ".
                                             "(SELECT actions.action_id as night_alt_action_id, ".
                                             "actions.action_banner as night_alt_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.night_alt_action_id AND ".
                                             "roles.role_id='$role_id') as night_alt_actions";
                                    if($row2 = mysqli_get_one($query)) {
                                        $action_id = $row2['night_action_id'];
                                        $banner_action = $row2['night_alt_action_id'];
                                        $banner = $row2['night_alt_action_banner'];
                                    }
                                }
    
                                if($player_ready == "Y") {
                                    //player ready, we need banner, banner_action, and un_ready
    
                                    //In case we want to start the game
                                    if($game_phase == 0 && $game_creator == $user_id) {
                                        $query = "SELECT * FROM actions WHERE action_enum='START'";
                                        if($row2 = mysqli_get_one($query)) {
                                            $banner = $row2['action_banner'];
                                            $banner_action = $row2['action_id'];
                                            $action_id = 0;
                                        } else {
                                            $banner = "ERROR"; //Should never hit here. If we do... let me know
                                        }
                                    }

                                    //Add UN_READY
                                    if($action_id != get_action_by_enum("NO_ACTION")) {
                                        $query = "SELECT * FROM actions WHERE action_enum='UN_READY'";
                                        if($row2 = mysqli_get_one($query)) {
                                            $alt_banner = $row2['action_banner'];
                                            $alt_banner_action = $row2['action_id'];
                                        }
                                    }
                                    $query2 = "SELECT action_id, target_id ".
                                             "FROM game_actions ".
                                             "WHERE game_id='$game_id' AND user_id='$user_id' AND ".
                                             "game_phase='$game_phase' AND game_turn='$game_turn'";
                                    if($row2 = mysqli_get_one($query)) {
                                        $set_action_id = $row2['action_id'];
                                        $set_target = $row2['target_id'];
                                        $to_return .= "<target>".get_user_name($set_target)."</target>\n";
                                    }
                                }
                            }
                        }
                        //And before this
    
                        $to_return .= "</player>\n";
                    }
                    if(!$real_channel) {
                        $real_channel = "No";
                    }
                    $to_return .= "<channel>$real_channel</channel>\n";
                }
                $to_return .= "</player_list>\n";
                if($action_id) {
                    $to_return .= "<action>$action_id</action>\n";
                }
                if($banner) {
                    $to_return .= "<banner>$banner</banner>\n";
                    $to_return .= "<banner_action>$banner_action</banner_action>\n";
                }
                if($alt_banner) {
                    $to_return .= "<alt_banner>$alt_banner</alt_banner>\n";
                    $to_return .= "<alt_banner_action>$alt_banner_action</alt_banner_action>\n";
                }
            }
            if(!$user_belongs) {
                //This is taken care of on the game screen, no need to increase our AJAX load.
            }
        }
        $to_return .= "</game_data>\n";
        return $to_return;
    }

    function kill_player($user_id, $game_id) {
        global $dbh;
        $user_name = get_user_name($user_id);
        if($user_name) {
            $query = "UPDATE channel_members, channels ".
                     "SET channel_members.channel_post_rights='0' ".
                     "WHERE channel_members.user_id='$user_id' AND ".
                     "channels.channel_id=channel_members.channel_id AND ".
                     "channels.game_id='$game_id'";
            if(mysqli_set_many($query)) {
                //Success
                $query = "UPDATE game_players ".
                         "SET player_alive='N' ".
                         "WHERE user_id='$user_id' AND game_id='$game_id'";
                if(mysqli_set_one($query)) {
                    update_game_tracker($game_id);
                    update_game_players($game_id);
                    //Success
                } else {
                    echo "Error setting player to dead. " . $query;
                }
            } else {
                echo "Error setting post rights to null. " . $query;
            }
        }
    }


?>
