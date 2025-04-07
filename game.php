<!--User will be brought here from the welcome page-->
<?php
    session_start();
    include 'game_logic.php';

    $prices = [.01, 1, 5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 750, 1000, 5000, 10000, 25000,
            50000, 75000, 100000, 200000, 300000, 400000, 500000, 750000, 1000000]; 
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
                    $caseNum = 1;
                    for ($i = 0; $i < 6; $i++) {
                        if ($caseNum == 26) {
                            echo '<div class="price-container" style="padding: 1%">';
                            echo '<form method="POST" id="form">';
                            echo '<button type="submit" class="case-button">';
                            echo '<div class="case-and-number">';
                            echo '<img src="assets/images/brief-case.png" class="cases" alt="" style="width: 15%; height: 15%;">';
                            echo '<div class="case-number">' . $caseNum . '</div>';
                            echo '</div>';
                            echo '</button>';
                            echo '</form>';
                            echo '</div>';
                            break;
                        }
                        echo '<div class="price-container" style="padding: 1%">';
                        for ($j = 0; $j < 5; $j++) {
                            echo '<form method="POST" id="form">';
                            echo '<button type="submit" class="case-button">';
                            echo '<div class="case-and-number">';
                            echo '<img src="assets/images/brief-case.png" class="cases" alt="">';
                            echo '<div class="case-number">' . $caseNum . '</div>';
                            echo '</div>';
                            echo '</button>';
                            echo '</form>';
                            $caseNum++;
                        }
                        echo '</div>';
                    }
                ?> 
                <div id="bottom-case-area">
                    <img src="assets/images/brief-case.png" class="player-case" alt="" style="width: 15%; height: 15%; margin-left: 5%;">
                </div> 
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