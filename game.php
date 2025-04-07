<!--User will be brought here from the welcome page-->
<?php
    $prices = [.01, 1, 5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 750, 1000, 5000, 10000, 25000,
            50000, 75000, 100000, 200000, 300000, 400000, 500000, 750000, 1000000];      
    if (isset($_POST['score'])) {
        $earnings = $_POST['score'];
        setcookie("earnings", $earnings, time() + 3600 + 24 + 30);
    }
    if (isset($_COOKIE['name']) && (isset($_COOKIE['earnings']))) {
        $name = $_COOKIE['name'];
        $earnings = $_COOKIE['earnings'];
        
        $file = 'leaderboard.txt';
        $leaderboardEntry = "$name, $earnings\n";
        file_put_contents($file, $leaderboardEntry, FILE_APPEND);
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Document</title>
</head>

<body>
    <img src="assets/images/brief-case.png" alt="">

    <div id="main-content">
        <p> Deal or No Deal</p>

        <div id="game-main-content">


            <div id="left-prices">
                <?php

                for ($i = 0; $i < 13; $i++) {
                        echo '<div class="price-container">' . "$" . number_format($prices[$i]) . '</div>';
                    }

                ?>
            </div>

            <div id="case-area">

                       <?php

                for ($i = 0; $i < 3; $i++){
                        echo '<div class="price-container"> 
                         <img src="assets/images/brief-case.png" alt="">
                         <img src="assets/images/brief-case.png" alt="">
                         <img src="assets/images/brief-case.png" alt="">
                         <img src="assets/images/brief-case.png" alt="">
                         <img src="assets/images/brief-case.png" alt="">
                        </div>';
                    }

                ?>  

            </div>

            <div id="right-prices">

                <?php

                for ($i = 13; $i < 26; $i++){
                        echo '<div class="price-container">' . "$" . number_format($prices[$i]) . '</div>';
                    }

                ?>

            </div>


        </div>
    </div>





</body>

</html>