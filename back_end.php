<?php
require "DBManager.php";
insertReplays();

function insertReplays(){
    $servername = "localhost";
    $username = "hots_stats";
    $password = "123";
    $dbname = "hots_stats";


    try{
        $dbManager = new DBManager($servername, $username, $password, $dbname);

        $tracker = $dbManager->getTracker();

        $url = 'http://hotsapi.net/api/v1/replays?min_id='.$tracker.'&start_date=2017-09-18&game_type=HeroLeague';
        $content = file_get_contents($url);
        $json = json_decode($content, true);
        $dbManager->prepareReplay();

//        $dbManager->beginTransaction();

        for ($i = 0; $i <6; $i++) {

            $replay_id = $json[$i]['id'];
            $game_type = $json[$i]['game_type'];
            $game_date = $json[$i]['game_date'];
            $game_map = $json[$i]['game_map'];
            $game_version = $json[$i]['game_version'];
            $region = $json[$i]['region'];
            $processed = $json[$i]['processed'];
            $dbManager->bindReplayParam($replay_id, $game_date, $game_type, $game_map, $game_version, $region, $processed);
            $replayExists = $dbManager->checkReplayExistsById($replay_id);
//            $dbManager->executeInsertReplay();
            if ($replayExists) {
                if ($processed) {
                    echo $json[$i]['id'] . " was processed, adding to replays and dumpSingleReplay gets called<br>";
                    $dbManager->executeInsertReplay();
                    $dbManager->insertPlayedHeroes($replay_id);

                } else {
                    echo $json[$i]['id'] . " wasn't processed, only adding to replays<br>";
                    $dbManager->executeInsertReplay();

                }
            }
            $last_id = $replay_id + 1;
            $dbManager->updateTracker($last_id);
        }
//        $dbManager->commit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
//        $dbManager->rollBack();
    }
    $dbManager->closeConn();
}

?>

