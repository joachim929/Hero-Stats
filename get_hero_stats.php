<?php

$servername = "localhost";
$username = "<insert username>";
$password = "<insert password>";
$dbname = "hots_stats";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

try{
    $getHeroStats = $conn->prepare("SELECT * FROM hero_stats ORDER BY win_rate DESC;");
    $getHeroStats->execute();
    $getHeroStats = $getHeroStats->fetchAll(PDO::FETCH_ASSOC);

    $getHeroStats = json_encode($getHeroStats);

    echo $getHeroStats;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

