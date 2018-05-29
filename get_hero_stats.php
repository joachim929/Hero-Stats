<?php
/**
 * Created by PhpStorm.
 * User: Tobi
 * Date: 2/27/2018
 * Time: 1:45 PM
 */
$servername = "localhost";
$username = "hots_stats";
$password = "123";
$dbname = "hots_stats";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

try{
    $getHeroStats = $conn->prepare("SELECT * FROM hero_stats ORDER BY win_rate DESC;");
    $getHeroStats->execute();
    $getHeroStats = $getHeroStats->fetchAll(PDO::FETCH_ASSOC);
// from here on out is testing
    $getHeroStats = json_encode($getHeroStats);
//    $filename = 'hero_stats.json';
//    $handle = fopen($filename, 'w+');
//    fwrite($handle, $formattedData);
//    fclose($handle);
    echo $getHeroStats;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

