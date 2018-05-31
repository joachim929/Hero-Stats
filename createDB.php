<?php
$servername = "localhost";
$username = "<insert username>";
$password = "<insert password>";
$dbname = "hots_stats";
/* Used to create Database with ease, saves a lot of time if something goes wrong during testing */
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $createStmt = $conn->prepare("


        CREATE TABLE replays (
            id INT AUTO_INCREMENT PRIMARY KEY,
            replay_id INT,
            game_date DATETIME,
            game_type VARCHAR(40),
            game_map VARCHAR(40),
            game_version VARCHAR(20),
            region INT,
            processed TINYINT(1),
            used TINYINT(1),
            UNIQUE(replay_id)
        );
        
        CREATE TABLE hero_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hero_name VARCHAR(20),
            play_count INT,
            win_count INT,
            win_rate FLOAT,
            UNIQUE(hero_name)
        );
        
        CREATE TABLE played_heroes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            replay_id INT,
            hero_id INT,
            hero_name VARCHAR(20),
            hero_level INT, 
            team TINYINT(1),
            winner TINYINT(1),
            silenced TINYINT(1),
            battletag VARCHAR(40),
            talent_one VARCHAR(40),
            talent_four VARCHAR(40),
            talent_seven VARCHAR(40),
            talent_ten VARCHAR(40),
            talent_thirteen VARCHAR(40),
            talent_sixteen VARCHAR(40),
            talent_twenty VARCHAR(40),
            score_level INT,
            score_kills INT,
            score_assists INT,
            score_takedowns INT,
            score_deaths INT,
            score_highest_kill_streak INT,
            score_hero_damage INT,
            score_siege_damage INT,
            score_structure_damage INT,
            score_minion_damage INT,
            score_creep_damage INT,
            score_summon_damage INT,
            score_time_cc_enemy_heroes INT,
            score_healing INT,
            score_self_healing INT,
            score_damage_taken INT,
            score_experience_contribution INT,
            score_town_kills INT,
            score_time_spent_dead INT,
            score_merc_camp_captures INT,
            meta_experience INT,
            FOREIGN KEY (replay_id) REFERENCES replays(replay_id),
            FOREIGN KEY (hero_id) REFERENCES hero_stats(id)
        );
        
        CREATE TABLE replay_tracker (
            id INT AUTO_INCREMENT PRIMARY KEY,
            last_id INT
        );
        
        CREATE TABLE bans(
            id INT AUTO_INCREMENT PRIMARY KEY,
            replay_id INT,
            hero_id INT,
            ban_name VARCHAR(20),
            FOREIGN KEY (hero_id) REFERENCES hero_stats(id),
            FOREIGN KEY (replay_id) REFERENCES replays(replay_id)
        );
        
        INSERT INTO `replay_tracker` (`id`, `last_id`) VALUES (NULL, '2522619');
        
    ");
    $createStmt->execute();
    unset($createStmt);
    $url = 'http://hotsapi.net/api/v1/heroes';
    $content = file_get_contents($url);
    $json = json_decode($content, true);

    $insert_hero_stats = $conn->prepare("INSERT INTO hero_stats (hero_name, play_count, win_count, win_rate) VALUES (:hero_name, :play_count, :win_count, :win_rate) ON DUPLICATE KEY UPDATE id=id");
    $insert_hero_stats->bindParam(':hero_name', $hero_name);
    $insert_hero_stats->bindParam(':play_count', $play_count);
    $insert_hero_stats->bindParam(':win_count', $win_count);
    $insert_hero_stats->bindParam(':win_rate', $win_rate);

    for($i= 0; $i<count($json); $i++){
        $hero_name = $json[$i]['name'];
        $play_count = 0;
        $win_count = 0;
        $win_rate = 0;
        $insert_hero_stats->execute();
    }


}
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
}
$conn = null;



?>