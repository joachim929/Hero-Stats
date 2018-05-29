$.ajax({
    url: "get_hero_stats.php"
})
    .done(function( data ) {
        if ( console && console.log ) {
            hero_stats = JSON.parse(data);
            console.log(hero_stats);



            printHeroStats(hero_stats);


        }
    });

function printHeroStats(hero_stats){
    document.write("<table width='75%'>");
    document.write("<tr><td>Hero Name</td><td>Play count</td><td>Win rate</td></tr>")
    // document.write("<tr><td>" + hero_stats[i]['hero_name'] + "<td>" + hero_stats[i]['win_rate'] + "</td></td></tr>")
    for(var i = 0; i < hero_stats.length; i++){
        document.write("<tr><td>" + hero_stats[i]['hero_name'] + "</td><td>" + hero_stats[i]['play_count'] + "</td><td>" + hero_stats[i]['win_rate'] + "%</td></tr>")
    }
    document.write("</table>");
}
