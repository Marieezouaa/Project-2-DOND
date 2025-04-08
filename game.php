<!--User will be brought here from the welcome page-->
<?php
    session_start();
    include 'game_logic.php';

    $prices = [.01, 1, 5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 750, 1000, 5000, 10000, 25000,
            50000, 75000, 100000, 200000, 300000, 400000, 500000, 750000, 1000000]; 
    
    // Get current game state
    $gameState = get_game_state();
    $moneyStatus = get_money_board_status();
    
    // Add debug output to track what's happening with case selection
    $debugOutput = '';
    if (isset($_SESSION['game']) && isset($_SESSION['game']['player_case'])) {
        $debugOutput = 'Player Case: ' . $_SESSION['game']['player_case'];
    } else {
        $debugOutput = 'No player case selected';
    }
    
    // Process form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle play again request
        if (isset($_POST['play_again'])) {
            // Save the player's name before resetting
            $playerName = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : 'Anonymous';
            
            // Start a new game
            start_new_game();
            
            // Restore the player's name
            $_SESSION['player_name'] = $playerName;
            
            // Redirect to prevent form resubmission
            header('Location: game.php');
            exit;
        }
        
        // Handle quit game request
        if (isset($_POST['quit_game'])) {
            // Reset game
            start_new_game();
            
            // Redirect to prevent form resubmission
            header('Location: game.php');
            exit;
        }
        
        // Store name if it's in the session
        if (isset($_SESSION['name'])) {
            $_SESSION['player_name'] = $_SESSION['name'];
        }
        
        // If a case number was submitted
        if (isset($_POST['case_number'])) {
            $caseNumber = (int)$_POST['case_number'];
            
            // Check if player case is already set
            if (!isset($_SESSION['game']['player_case']) || $_SESSION['game']['player_case'] === null) {
                // First case selection - player is choosing their case
                $_SESSION['game']['player_case'] = $caseNumber;
                
                // Important: Remove player's case from the list of remaining cases
                // This might be missing in the original implementation
                if (($key = array_search($caseNumber, $_SESSION['game']['remaining_cases'])) !== false) {
                    // Don't remove it from remaining cases - we want it to stay there
                    // Just mark it as the player's case
                }
                
                $debugOutput = "DIRECT UPDATE: Selected player case: " . $caseNumber;
            } else {
                // Player is opening a case (not their own)
                if ($caseNumber != $_SESSION['game']['player_case'] && 
                    in_array($caseNumber, $_SESSION['game']['remaining_cases'])) {
                    
                    $result = open_case($caseNumber);
                    if ($result && isset($result['end_of_round']) && $result['end_of_round']) {
                        // Store the banker offer for display
                        $_SESSION['current_offer'] = $result['banker_offer'];
                    }
                    
                    $debugOutput = "Opened case: " . $caseNumber;
                } else {
                    $debugOutput = "Invalid case selection: " . $caseNumber;
                }
            }
        }
        
        // Handle banker decision
        if (isset($_POST['banker_decision'])) {
            $decision = $_POST['banker_decision'];
            $result = handle_banker_offer($decision);
            
            // If game finished, redirect to leaderboard
            if ($result && isset($result['accepted']) && $result['accepted']) {
                header('Location: leaderboard.php');
                exit;
            }
        }
        
        // Handle case switch at end
        if (isset($_POST['switch_case'])) {
            $switch = ($_POST['switch_case'] === 'yes');
            $result = switch_case($switch);
            
            // Redirect to leaderboard
            header('Location: leaderboard.php');
            exit;
        }
        
        // Prevent form resubmission
        header('Location: game.php');
        exit;
    }
    
    // Update the game state after processing
    $gameState = get_game_state();
    
    // Check for banker offer to display
    $showBankerOffer = false;
    $currentOffer = 0;
    
    if (isset($_SESSION['current_offer'])) {
        $showBankerOffer = true;
        $currentOffer = $_SESSION['current_offer'];
        unset($_SESSION['current_offer']); // Clear it after displaying
    }
    
    // Check if we're in the final case switch phase
    $finalPhase = false;
    if (isset($gameState['remaining_cases']) && count($gameState['remaining_cases']) == 2 && 
        $gameState['current_round_cases_opened'] == 0 && !$gameState['game_finished']) {
        $finalPhase = true;
    }
    
    // Check if game is finished
    $gameFinished = isset($gameState['game_finished']) && $gameState['game_finished'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Document</title>
    <style>
        /* Add some minimal styling for functionality */
        .case-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }
        
        .case-opened {
            opacity: 0.5;
        }
        
        .banker-offer {
            background-color: rgba(255, 228, 181, 0.9);
            border: 2px solid #daa520;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 500px;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        
        .offer-buttons button {
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .deal-button {
            background-color: green;
            color: white;
            border: none;
        }
        
        .no-deal-button {
            background-color: red;
            color: white;
            border: none;
        }
        
        .price-container.removed {
            text-decoration: line-through;
            opacity: 0.5;
        }
        
    </style>
</head>

<body>

    
    <div id="main-content">
        <p> Deal or No Deal</p>

        <?php if ($showBankerOffer): ?>
        <div class="banker-offer">
            <h2>Banker's Offer</h2>
            <p style="font-size: 24px; font-weight: bold;"><?php echo format_money($currentOffer); ?></p>
            <div class="offer-buttons">
                <form method="post" action="game.php">
                    <input type="hidden" name="banker_decision" value="deal">
                    <button type="submit" class="deal-button">DEAL</button>
                </form>
                <form method="post" action="game.php">
                    <input type="hidden" name="banker_decision" value="no_deal">
                    <button type="submit" class="no-deal-button">NO DEAL</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($finalPhase): ?>
        <div class="banker-offer">
            <h2>Final Decision</h2>
            <p>Do you want to switch your case with the last remaining case?</p>
            <div class="offer-buttons">
                <form method="post" action="game.php">
                    <input type="hidden" name="switch_case" value="yes">
                    <button type="submit">YES, SWITCH</button>
                </form>
                <form method="post" action="game.php">
                    <input type="hidden" name="switch_case" value="no">
                    <button type="submit">NO, KEEP MY CASE</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($gameFinished): ?>
        <div class="banker-offer">
            <h2>Game Over!</h2>
            <p>You won: <?php echo format_money($gameState['final_winnings']); ?></p>
            <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                <form action="leaderboard.php" method="post">
                    <button type="submit">View Leaderboard</button>
                </form>
                <form action="game.php" method="post">
                    <input type="hidden" name="play_again" value="1">
                    <button type="submit">Play Again</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div id="game-main-content">
            <div id="left-prices">
                <?php
                for ($i = 0; $i < 13; $i++) {
                    $class = 'price-container';
                    if (isset($moneyStatus[$prices[$i]]) && $moneyStatus[$prices[$i]] === 'removed') {
                        $class .= ' removed';
                    }
                    echo '<div class="' . $class . '">' . "$" . number_format($prices[$i]) . '</div>';
                }
                ?>
            </div>

            <div id="case-area">
                <?php
                    $caseNum = 1;
                    $playerCase = isset($gameState['player_case']) ? $gameState['player_case'] : null;
                    $openedCases = isset($gameState['opened_cases']) ? $gameState['opened_cases'] : [];
                    
                    for ($i = 0; $i < 6; $i++) {
                        if ($caseNum == 26) {
                            echo '<div class="price-container" style="padding: 1%">';
                            
                            // Check if this case can be selected
                            $caseClass = 'cases';
                            $disabled = '';
                            
                            // Determine if this case is opened or the player's case
                            if (array_key_exists($caseNum, $openedCases)) {
                                $caseClass .= ' case-opened';
                                $disabled = 'disabled';
                            } elseif ($caseNum == $playerCase) {
                                // Don't add any visual class, but disable the button
                                $disabled = 'disabled';
                            }
                            
                            echo '<form method="POST" action="game.php" id="form">';
                            echo '<input type="hidden" name="case_number" value="' . $caseNum . '">';
                            echo '<button type="submit" class="case-button" ' . $disabled . '>';
                            echo '<div class="case-and-number">';
                            echo '<img src="assets/images/brief-case.png" class="' . $caseClass . '" alt="" style="width: 15%; height: 15%;">';
                            echo '<div class="case-number">' . $caseNum . '</div>';
                            echo '</div>';
                            echo '</button>';
                            echo '</form>';
                            echo '</div>';
                            break;
                        }
                        
                        echo '<div class="price-container" style="padding: 1%">';
                        
                        for ($j = 0; $j < 5; $j++) {
                            // Check if this case can be selected
                            $caseClass = 'cases';
                            $disabled = '';
                            
                            // Determine if this case is opened or the player's case
                            if (array_key_exists($caseNum, $openedCases)) {
                                $caseClass .= ' case-opened';
                                $disabled = 'disabled';
                            } elseif ($caseNum == $playerCase) {
                                // Don't add any visual class, but disable the button
                                $disabled = 'disabled';
                            }
                            
                            echo '<form method="POST" action="game.php" id="form">';
                            echo '<input type="hidden" name="case_number" value="' . $caseNum . '">';
                            echo '<button type="submit" class="case-button" ' . $disabled . '>';
                            echo '<div class="case-and-number">';
                            echo '<img src="assets/images/brief-case.png" class="' . $caseClass . '" alt="">';
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
                    <?php if ($playerCase !== null): ?>
                        <div style="display:flex; align-items:center; justify-content:center; margin-top:20px;">
                            <img src="assets/images/brief-case.png" class="player-case" alt="" style="width: 15%; height: 15%;">
                            <span style="margin-left:10px; font-weight:bold;">Your Case: <?php echo $playerCase; ?></span>
                        </div>
                    <?php else: ?>
                        <h3 style="text-align: center;">Select Your Case</h3>
                    <?php endif; ?>
                    
                    <!-- Quit Game Button -->
                    <div style="text-align: center; margin-top: 20px;">
                        <form action="game.php" method="post">
                            <input type="hidden" name="quit_game" value="1">
                            <button type="submit" style="background-color: #f44336; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Quit Game</button>
                        </form>
                    </div>
                </div> 
            </div>

            <div id="right-prices">
                <?php
                for ($i = 13; $i < 26; $i++){
                    $class = 'price-container';
                    if (isset($moneyStatus[$prices[$i]]) && $moneyStatus[$prices[$i]] === 'removed') {
                        $class .= ' removed';
                    }
                    echo '<div class="' . $class . '">' . "$" . number_format($prices[$i]) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>