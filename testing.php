<?php
/**
 * Created by PhpStorm.
 * User: J
 * Date: 25/02/2018
 * Time: 13:31
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
    insertReplay($json, $conn);
    $replays = getReplays($conn);
    processReplays($conn, $replays);



}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $conn->rollBack();
}

function processSingleReplay($conn, $json, $replay_id){
    $conn->beginTransaction();
    for($i = 0; $i<count($json['bans']); $i++){
        for($j = 0; $j<count($json['bans'][0]); $j++){
            $hero_name = $json['bans'][$i][$j];
            $insertBan = $conn->prepare("INSERT INTO bans (replay_id, hero_name) VALUES ($replay_id, $hero_name)");
            $insertBan->bindParam(':replay_id', $replay_id);
            $insertBan->bindParam(':hero_name', $hero_name);
            $insertBan->execute();
        }
    }
    echo "<br><br>";
    $conn->commit();
}

function processReplays($conn, $replays){
    for($i = 0; $i<count($replays); $i++){
        $replay_id = $replays[$i][0];
        $url = 'http://hotsapi.net/api/v1/replays/'.$replays[$i][0];
        $content = file_get_contents($url);
        $json = json_decode($content, true);
        processSingleReplay($conn, $json, $replay_id);
    }
}

function insertReplay($json, $conn){
    $conn->beginTransaction();
    for($i = 0; $i<count($json); $i++){
        $replay_id = $json[$i]['id'];
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
    $conn->commit();
}
function getReplays($conn){
    $getReplays = $conn->prepare("SELECT replay_id FROM replays WHERE used = 0 LIMIT 100");
    $getReplays->execute();
    $result = $getReplays->fetchAll();
    return $result;
}

?>
