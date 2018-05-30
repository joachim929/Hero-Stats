<?php

class DBManager
{
    /**
     * @var PDO
     */
    private $conn;
    /**
     * @var PDOStatement
     */
    private $insertReplay;


    function __construct($servername, $username, $password, $dbname)
    {
        $this->conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }

    function closeConn(){

        $conn = null;
    }
    /**
     * @return int replayId
     *
     */
    function getTracker(){
        $replay_tracker = $this->conn->prepare("SELECT last_id FROM replay_tracker");
        $replay_tracker->execute();
        $result_tracker = $replay_tracker->fetchAll();

        return $result_tracker[0][0];
    }

    /**
     * @param int $last_id
     */
    function updateTracker($last_id){
        $tracker_update = $this->conn->prepare("UPDATE replay_tracker SET `last_id` = :last_id WHERE id=1");
        $tracker_update->bindParam(':last_id',$last_id);
        $tracker_update->execute();

    }

    /**
     * @param int $replayId
     * @param dateTime $gameDate
     * @param string $gameType
     * @param string $gameMap
     * @param string $gameVersion
     * @param int $region
     * @param bool $processed
     */

    function bindReplayParam($replayId, $gameDate, $gameType, $gameMap, $gameVersion, $region, $processed){
        $this->insertReplay->bindParam(':replay_id', $replayId);
        $this->insertReplay->bindParam(':game_date', $gameDate);
        $this->insertReplay->bindParam(':game_type', $gameType);
        $this->insertReplay->bindParam(':game_map', $gameMap);
        $this->insertReplay->bindParam(':game_version', $gameVersion);
        $this->insertReplay->bindParam(':region', $region);
        $this->insertReplay->bindParam(':processed', $processed);

    }
    function prepareReplay(){
        $this->insertReplay = $this->conn->prepare("INSERT INTO replays (replay_id, game_date, game_type, game_map, game_version, region, processed)
            VALUES (:replay_id, :game_date, :game_type, :game_map, :game_version, :region, :processed)");

    }

    function executeInsertReplay(){
        $this->insertReplay->execute();

    }

    /**
     * @param int $replayId
     * @return bool
     */
    function checkReplayExistsById($replayId){
        $get_replays =  $this->conn->prepare("SELECT * FROM replays WHERE replay_id = :replay_id");
        $get_replays->bindParam(':replay_id', $replayId);
        $get_replays->execute();
        $get_replays = $get_replays->fetchAll();
        if(isset($get_replays[0]['replay_id'])){
            return false;
        }
        return true;
    }

    function insertPlayedHeroes(int $replayId){
        $url = 'http://hotsapi.net/api/v1/replays/'.$replayId;
        $content = file_get_contents($url);
        $json = json_decode($content, true);
        $hero_stats_array = $this->getHeroes();

        for($i = 0; $i<count($json['players']); $i++){
            $hero_name = $json['players'][$i]['hero'];
            $heroes_column = array_column($hero_stats_array, 'hero_name');
            $found_key = array_search($hero_name, $heroes_column);
            if($found_key == NULL){
                //Creates new hero if it doesn't exist
                $this->createNewHero($json, $i);
                echo "new entry created for ".$hero_name."<br>";
                $hero_stats_array = $this->getHeroes();
            }
            else{
                //Updates hero if its already in DB
                $calcUpdateHeroReturn = $this->calcUpdateHero($json, $i, $hero_stats_array, $found_key);
                $win_count = $calcUpdateHeroReturn[0];
                $play_count = $calcUpdateHeroReturn[1];
                $win_rate = $calcUpdateHeroReturn[2];

                $this->prepareHeroUpdate();
                $this->bindHeroUpdateParam($hero_name, $win_count, $play_count, $win_rate);
                $this->executeHeroUpdate();
            }

            $replay_id = $json['id'];
            $hero_id = $found_key+1;
            $hero_name = $json['players'][$i]['hero'];
            $hero_level = $json['players'][$i]['hero_level'];
            $team = $json['players'][$i]['team'];
            $winner = $json['players'][$i]['winner'];
            $silenced = $json['players'][$i]['silenced'];
            $battletag = $json['players'][$i]['battletag'];
            $talent_one = $this->doesTalentExist($json['players'][$i]['talents'], 1);
            $talent_four = $this->doesTalentExist($json['players'][$i]['talents'], 4);
            $talent_seven = $this->doesTalentExist($json['players'][$i]['talents'], 7);
            $talent_ten = $this->doesTalentExist($json['players'][$i]['talents'], 10);
            $talent_thirteen = $this->doesTalentExist($json['players'][$i]['talents'], 13);
            $talent_sixteen = $this->doesTalentExist($json['players'][$i]['talents'], 16);
            $talent_twenty = $this->doesTalentExist($json['players'][$i]['talents'], 20);
            $score_level = $json['players'][$i]['score']['level'];
            $score_kills = $json['players'][$i]['score']['kills'];
            $score_assists = $json['players'][$i]['score']['assists'];
            $score_takedowns = $json['players'][$i]['score']['takedowns'];
            $score_deaths = $json['players'][$i]['score']['deaths'];
            $score_highest_kill_streak = $json['players'][$i]['score']['highest_kill_streak'];
            $score_hero_damage = $json['players'][$i]['score']['hero_damage'];
            $score_siege_damage = $json['players'][$i]['score']['siege_damage'];
            $score_structure_damage = $json['players'][$i]['score']['structure_damage'];
            $score_minion_damage = $json['players'][$i]['score']['minion_damage'];
            $score_creep_damage = $json['players'][$i]['score']['creep_damage'];
            $score_summon_damage = $json['players'][$i]['score']['summon_damage'];
            $score_time_cc_enemy_heroes = $json['players'][$i]['score']['time_cc_enemy_heroes'];
            $score_healing = $json['players'][$i]['score']['healing'];
            $score_self_healing = $json['players'][$i]['score']['self_healing'];
            $score_damage_taken = $json['players'][$i]['score']['damage_taken'];
            $score_experience_contribution = $json['players'][$i]['score']['experience_contribution'];
            $score_town_kills = $json['players'][$i]['score']['town_kills'];
            $score_time_spent_dead = $json['players'][$i]['score']['time_spent_dead'];
            $score_merc_camp_captures = $json['players'][$i]['score']['merc_camp_captures'];
            $meta_experience = $json['players'][$i]['score']['meta_experience'];



            $this-> preparePlayedHeroes($replay_id, $hero_id, $hero_name, $hero_level, $team, $winner, $silenced, $battletag, $talent_one, $talent_four, $talent_seven, $talent_ten, $talent_thirteen, $talent_sixteen,
                $talent_twenty, $score_level, $score_kills, $score_assists, $score_takedowns, $score_deaths, $score_highest_kill_streak, $score_hero_damage, $score_siege_damage, $score_structure_damage,
                $score_minion_damage, $score_creep_damage, $score_summon_damage, $score_time_cc_enemy_heroes, $score_healing, $score_self_healing, $score_damage_taken, $score_experience_contribution,
                $score_town_kills, $score_time_spent_dead, $score_merc_camp_captures, $meta_experience);
        }


    }

    /**
     * @param array $talentInQuestion
     * @param int $j
     * @return null
     */
    function doesTalentExist($talentInQuestion, int $j){
        if(isset($talentInQuestion[$j])){
            $talent = $talentInQuestion[$j];
        }else{
            $talent = null;
        }
        return $talent;
    }

    function preparePlayedHeroes($replay_id, $hero_id, $hero_name, $hero_level, $team, $winner, $silenced, $battletag, $talent_one, $talent_four, $talent_seven, $talent_ten, $talent_thirteen, $talent_sixteen,
                                 $talent_twenty, $score_level, $score_kills, $score_assists, $score_takedowns, $score_deaths, $score_highest_kill_streak, $score_hero_damage, $score_siege_damage, $score_structure_damage,
                                 $score_minion_damage, $score_creep_damage, $score_summon_damage, $score_time_cc_enemy_heroes, $score_healing, $score_self_healing, $score_damage_taken, $score_experience_contribution,
                                 $score_town_kills, $score_time_spent_dead, $score_merc_camp_captures, $meta_experience){
        $this->insertPlayedHeroes = $this->conn->prepare("INSERT INTO played_heroes (replay_id, hero_id, hero_name, hero_level, team, winner, silenced, battletag, talent_one, talent_four, talent_seven, 
                                talent_ten, talent_thirteen, talent_sixteen, talent_twenty, score_level, score_kills, score_assists, score_takedowns, score_deaths, score_highest_kill_streak, score_hero_damage,
                                score_siege_damage, score_structure_damage, score_minion_damage, score_creep_damage, score_summon_damage, score_time_cc_enemy_heroes, score_healing, score_self_healing, score_damage_taken,
                                score_experience_contribution, score_town_kills, score_time_spent_dead, score_merc_camp_captures, meta_experience) 
                                VALUES (:replay_id, :hero_id, :hero_name, :hero_level, :team, :winner, :silenced, :battletag, :talent_one, :talent_four, :talent_seven, 
                                :talent_ten, :talent_thirteen, :talent_sixteen, :talent_twenty, :score_level, :score_kills, :score_assists, :score_takedowns, :score_deaths, :score_highest_kill_streak, :score_hero_damage,
                                :score_siege_damage, :score_structure_damage, :score_minion_damage, :score_creep_damage, :score_summon_damage, :score_time_cc_enemy_heroes, :score_healing, :score_self_healing, :score_damage_taken,
                                :score_experience_contribution, :score_town_kills, :score_time_spent_dead, :score_merc_camp_captures, :meta_experience)");
        $this->insertPlayedHeroes->bindParam(':replay_id', $replay_id);
        $this->insertPlayedHeroes->bindParam(':hero_id', $hero_id);
        $this->insertPlayedHeroes->bindParam(':hero_name', $hero_name);
        $this->insertPlayedHeroes->bindParam(':hero_level', $hero_level);
        $this->insertPlayedHeroes->bindParam(':team', $team);
        $this->insertPlayedHeroes->bindParam(':winner', $winner);
        $this->insertPlayedHeroes->bindParam(':silenced', $silenced);
        $this->insertPlayedHeroes->bindParam(':battletag', $battletag);
        $this->insertPlayedHeroes->bindParam(':talent_one', $talent_one);
        $this->insertPlayedHeroes->bindParam(':talent_four', $talent_four);
        $this->insertPlayedHeroes->bindParam(':talent_seven', $talent_seven);
        $this->insertPlayedHeroes->bindParam(':talent_ten', $talent_ten);
        $this->insertPlayedHeroes->bindParam(':talent_thirteen', $talent_thirteen);
        $this->insertPlayedHeroes->bindParam(':talent_sixteen', $talent_sixteen);
        $this->insertPlayedHeroes->bindParam(':talent_twenty', $talent_twenty);
        $this->insertPlayedHeroes->bindParam(':score_level', $score_level);
        $this->insertPlayedHeroes->bindParam(':score_kills', $score_kills);
        $this->insertPlayedHeroes->bindParam(':score_assists', $score_assists);
        $this->insertPlayedHeroes->bindParam(':score_takedowns', $score_takedowns);
        $this->insertPlayedHeroes->bindParam(':score_deaths', $score_deaths);
        $this->insertPlayedHeroes->bindParam(':score_highest_kill_streak', $score_highest_kill_streak);
        $this->insertPlayedHeroes->bindParam(':score_hero_damage', $score_hero_damage);
        $this->insertPlayedHeroes->bindParam(':score_siege_damage', $score_siege_damage);
        $this->insertPlayedHeroes->bindParam(':score_structure_damage', $score_structure_damage);
        $this->insertPlayedHeroes->bindParam(':score_minion_damage', $score_minion_damage);
        $this->insertPlayedHeroes->bindParam(':score_creep_damage', $score_creep_damage);
        $this->insertPlayedHeroes->bindParam(':score_summon_damage', $score_summon_damage);
        $this->insertPlayedHeroes->bindParam(':score_time_cc_enemy_heroes', $score_time_cc_enemy_heroes);
        $this->insertPlayedHeroes->bindParam(':score_healing', $score_healing);
        $this->insertPlayedHeroes->bindParam(':score_self_healing', $score_self_healing);
        $this->insertPlayedHeroes->bindParam(':score_damage_taken', $score_damage_taken);
        $this->insertPlayedHeroes->bindParam(':score_experience_contribution', $score_experience_contribution);
        $this->insertPlayedHeroes->bindParam(':score_town_kills', $score_town_kills);
        $this->insertPlayedHeroes->bindParam(':score_time_spent_dead', $score_time_spent_dead);
        $this->insertPlayedHeroes->bindParam(':score_merc_camp_captures', $score_merc_camp_captures);
        $this->insertPlayedHeroes->bindParam(':meta_experience', $meta_experience);
        $this->insertPlayedHeroes->execute();
    }

    function getHeroes(){
        $get_heroes = $this->conn->prepare("SELECT * FROM hero_stats");
        $get_heroes->execute();
        $get_heroes = $get_heroes->fetchAll();
        return $get_heroes;
    }

    function createNewHero($json, $i){
        $this->insert_hero_stats = $this->conn->prepare("INSERT INTO hero_stats (hero_name, play_count, win_count) VALUES (:hero_name, :play_count, :win_count)");
        $this->insert_hero_stats->bindParam(':hero_name', $hero_name);
        $this->insert_hero_stats->bindParam(':play_count', $play_count);
        $this->insert_hero_stats->bindParam(':win_count', $win_count);

        $hero_name = $json['players'][$i]['hero'];
        $winner = $json['players'][$i]['winner'];
        $play_count = 1;
        $win_count = (boolean)$winner;

        $this->insert_hero_stats->execute();
    }

    function calcUpdateHero($json, $i, $hero_stats_array, $found_key){
        $winner = $json['players'][$i]['winner'];
        $win_count = $hero_stats_array[$found_key]['win_count'];
        $play_count = $hero_stats_array[$found_key]['play_count'];

        $win_count += $winner;
        $play_count += 1;
        $win_rate = round(($win_count / $play_count) *100, 1);
        return array($win_count, $play_count, $win_rate);

    }
    function bindHeroUpdateParam($hero_name, $win_count, $play_count, $win_rate){
        $this->updateHero->bindParam(':hero_name', $hero_name);
        $this->updateHero->bindParam(':win_count', $win_count);
        $this->updateHero->bindParam(':play_count', $play_count);
        $this->updateHero->bindParam(':win_rate', $win_rate);

    }
    function prepareHeroUpdate(){
        $this->updateHero = $this->conn->prepare("UPDATE hero_stats SET play_count = :play_count, win_count = :win_count, win_rate = :win_rate WHERE hero_name = :hero_name");

    }
    function executeHeroUpdate(){
        $this->updateHero->execute();

    }



}
?>