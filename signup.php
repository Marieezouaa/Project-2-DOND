<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    setcookie('name', $name, time() + 3600 * 24 * 30);
    
    $firstNlast = explode(" ", $name);
    $firstName = $firstNlast[0];
    
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

    <div id="main-content">

        <p id="ready">Hey<?php 
        if (!empty($firstName)) {
            echo ", " . $firstName . "! Are you ready to play?</p>";
        } else {
            echo "! Are you ready to play?</p>";
        }
        ?>

        <img id="dond-logo" src="assets/images/dond-logo.jpeg" alt="">

        <div id="navigation-btns">

            <a href="game.php">
                <div id="submit-btn-container">
                    <button class="option-btn" id="start-game-btn" type="submit">Deal!</button>
                </div>
            </a>

            <a href="rules.html">
                <div id="submit-btn-container">
                    <button class="option-btn" id="view-rules-btn" type="submit">Rules</button>
                </div>
            </a>
            <a href="creators.html">
                <div id="submit-btn-container">
                    <button class="option-btn" id="view-creators-btn" type="submit">Creators</button>
                </div>
            </a>
        </div>

    </div>






</body>

</html>