1)Using XAMPP create a database in localhost/phpmyadmin with the name of hots_stats

2)Save the repository in the XAMPP/htdocs folder

3)Change the $username and $password variables to your desired phpmyadmin username and password in the follow files:
    back_end.php,
    createDB.php,
    get_hero_stats.php,
    resetDB.php (optional)

3)Load http://localhost/Hero-Stats/createDB.php

4)To populate the database there are 2 options:
    1)You can either populate the database by importing some sample data with filename hots_stats.sql

    2)Or you can load http://localhost/Hero-Stats/back_end.php, this can be executed about 2 times a minute before getting a too many requests error (429), the work around is setting up a cron job.

5)To see stats on the web page load index.html
http://localhost/Hero-Stats/index.html
