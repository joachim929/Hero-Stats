<?php
/**
 * Created by PhpStorm.
 * User: J
 * Date: 26/02/2018
 * Time: 15:33
 */

$servername = "localhost";
$username = "hots_stats";
$password = "123";
$dbname = "hots_stats";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$url = 'http://hotsapi.net/api/v1/replays?min_id=2522619&start_date=2017-09-18&game_type=HeroLeague';
$content = file_get_contents($url);
$json = json_decode($content, true);

try{
    $get_replays = getReplaysDB($conn);
    $get_heroes = getHeroesDB($conn);
    if(empty($get_replays)){
        //get more replays
        echo "No replays unused, getting more<br>";
        getReplaysAPI($conn, $get_replays);
    }
    else{
        //process insert bans, single replays and update hero stats
        echo "Working through the data...<br>";
        processSingleReplay($conn, $get_replays, $get_heroes);
    }
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();

}

function processSingleReplay($conn, $get_replays, $get_heroes){

    for($i = 0; $i<count($get_replays); $i++){
        $url = 'http://hotsapi.net/api/v1/replays/'.$get_replays[$i][0];
        $content = file_get_contents($url);
        $json = json_decode($content, true);
        for($j = 0; $j<count($json['players']); $j++){

        }
    }

}

function bindSingleReplay($insertSingleReplay, $replay_id, $hero_id, $hero_name, $hero_level, $team, $winner, $talent_one, $talent_four, $talent_seven, $talent_ten, $talent_thirteen, $talent_sixteen, $talent_twenty){
    $insertSingleReplay->bindParam(':replay_id', $replay_id);
    $insertSingleReplay->bindParam(':hero_id', $hero_id);
    $insertSingleReplay->bindParam(':hero_name', $hero_name);
    $insertSingleReplay->bindParam(':hero_level',$hero_level);
    $insertSingleReplay->bindParam(':team',$team);
    $insertSingleReplay->bindParam(':winner',$winner);
    $insertSingleReplay->bindParam(':talent_one',$talent_one);
    $insertSingleReplay->bindParam(':talent_four',$talent_four);
    $insertSingleReplay->bindParam(':talent_seven',$talent_seven);
    $insertSingleReplay->bindParam(':talent_ten',$talent_ten);
    $insertSingleReplay->bindParam(':talent_thirteen',$talent_thirteen);
    $insertSingleReplay->bindParam(':talent_sixteen',$talent_sixteen);
    $insertSingleReplay->bindParam(':talent_twenty',$talent_twenty);


}

function insertReplays($conn, $json, $get_replays){

    $conn->beginTransaction();
    for($i = 0; $i<count($json); $i++){

        $replay_id = $json[$i]['id'];

        $replay_column = array_column($get_replays, 'replay_id');
        $found_key = array_search($replay_id, $replay_column);

        if(empty($found_key)){
            //insert date
            $game_date = $json[$i]['game_date'];
            $game_type = $json[$i]['game_type'];
            $game_map = $json[$i]['game_map'];
            $game_version = $json[$i]['game_version'];
            $region = $json[$i]['region'];
            $processed = $json[$i]['processed'];
            $used = 0;
            $insertReplay = $conn->prepare("INSERT INTO replays (replay_id) VALUES ($replay_id)");
            $insertReplay->bindParam(':replay_id', $replay_id);
            $updateReplay = $conn->prepare("UPDATE replays SET game_date = :game_date, game_type = :game_type, game_map = :game_map, game_version = :game_version, region = :region,
                                              processed = :processed, used = :used WHERE replay_id=$replay_id");
            $updateReplay->bindParam(':game_date',$game_date);
            $updateReplay->bindParam(':game_type', $game_type);
            $updateReplay->bindParam(':game_map', $game_map);
            $updateReplay->bindParam(':game_version', $game_version);
            $updateReplay->bindParam(':region', $region);
            $updateReplay->bindParam(':processed', $processed);
            $updateReplay->bindParam(':used', $used);
            $insertReplay->execute();
            $updateReplay->execute();
        }
        else {
            echo $replay_id." is already in DB<br>";
            break;
        }

    }
    $conn->commit();
}


function getReplaysAPI($conn, $get_replays){
    $getTracker = $conn->prepare("SELECT last_id FROM replay_tracker");
    $getTracker->execute();
    $getTracker = $getTracker->fetchAll();
    $getTracker = $getTracker[0][0];
//    $url = 'http://hotsapi.net/api/v1/replays?min_id='.$getTracker.'&start_date=2017-09-18&game_type=HeroLeague';
    //change to get tracker after testing is done
    $url = 'http://hotsapi.net/api/v1/replays?min_id=2522619&start_date=2017-09-18&game_type=HeroLeague';
    $content = file_get_contents($url);
    $json = json_decode($content, true);
    insertReplays($conn, $json, $get_replays);
}

function getReplaysDB($conn){
    $getReplays = $conn->prepare("SELECT replay_id FROM replays WHERE used = 0 LIMIT 20");
    $getReplays->execute();
    $getReplays = $getReplays->fetchAll();
    return $getReplays;
}

function getHeroesDB($conn){
    $getHeroes = $conn->prepare("SELECT * FROM hero_stats");
    $getHeroes->execute();
    $getHeroes = $getHeroes->fetchAll();
    return $getHeroes;
}
