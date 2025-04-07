<!--We can use this page for the leaderboard logic-->
<?php
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
    $file = 'leaderboard.txt';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        $lines = explode("\n", $data);
        $leaderboard = [];
        

        for ($i = 0; $i < count($lines); $i++) {
            list($name, $earnings) = explode(",", $lines[$i]);
            $leaderboard[] = ['name' => $name, 'earnings' => (int)$earnings];
        }
        
        usort($leaderboard, function($a, $b){
            return $b['earnings'] - $a['earnings'];            
        });
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="main-content">
        <h1 style="font-size: 30pt; margin: 1% 0 0 0;">Leaderboard</h1>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Earnings</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    for ($i = 0; $i < min(count($leaderboard),10); $i++) {
                        echo "<tr>";
                        echo "<td>" . ($i + 1) . "</td>";
                        echo "<td>" . $leaderboard[$i]['name'] . "</td>";
                        echo "<td>" . $leaderboard[$i]['earnings'] . "</td>";
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
        <div id="bottom">
            <a href="signup.php">
                <button id="home"><img src="assets/images/home.png"></button>
            </a>
        </div>
    </div>
    
</body>
</html>