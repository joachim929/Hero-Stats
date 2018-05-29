<?php
/**
 * Created by PhpStorm.
 * User: J
 * Date: 25/02/2018
 * Time: 15:59
 */
$servername = "localhost";
$username = "hots_stats";
$password = "123";
$dbname = "hots_stats";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

$url = 'http://hotsapi.net/api/v1/replays/18758';
$content = file_get_contents($url);
$json = json_decode($content, true);
$replay_id = 18758;
$getHeroes = $conn->prepare("SELECT id, hero_name FROM hero_stats");
$getHeroes->execute();
$getHeroes = $getHeroes->fetchAll();




$conn->beginTransaction();
for($i = 0; $i<count($json['bans']); $i++){
    for($j = 0; $j<count($json['bans'][0]); $j++){
        $ban_name = $json['bans'][$i][$j];
        $hero_column = array_column($getHeroes, 'hero_name');
        $found_key = array_search($ban_name, $hero_column);
        $hero_id = $found_key + 1;

        $insertBan = $conn->prepare("INSERT INTO bans (hero_id, replay_id) VALUES ($hero_id, $replay_id)");
        $insertBan->bindParam(':hero_id', $hero_id);
        $insertBan->bindParam(':replay_id', $replay_id);
        $insertBan->execute();
//        $updateBan = $conn->prepare("UPDATE bans SET replay_id = :replay_id, ban_name = :ban_name WHERE id = id");
    }
}

$conn->commit();


?>