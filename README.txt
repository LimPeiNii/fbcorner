1. the database data file "fbcorner.sql" is attached 
----only work for > MariaDb 10.4 version
----collation: utf8mb4_general_ci

2. "today_opening_closing_time.sql" is attached 
----for creating a function used to get restaurant's opening time (in database)

3. this project is using apache and php (xampp)


STEPS to setup:
1. copy the folder "fbcorner" containing all files into the "htdocs" folder
2. in phpmyadmin, create a database named "fbcorner" (collation: utf8mb4_general_ci) and import data from the "fbcorner.sql" attached (under import tab)
3. create a function in database by using the codes from "today_opening_closing_time.sql" attached (in phpmyadmin, run the code under SQL tab)
4. in the "db_connect.php" file, change the value for username and password accordingly


Customer side directory:

http://localhost/fbcorner/
username and password: register for a new account to set username and password



Admin side directory:

http://localhost/fbcorner/admin/
restaurant email, username and password: register for a new account to set the restaurant email, username and password
or
using this for testing purpose:
---restaurant email: pennylim2000@gmail.com
---username: admin
---password: 1234
