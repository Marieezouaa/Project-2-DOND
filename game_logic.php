<?php
// Start the session to persist game state across pages
session_start();

// Define the possible monetary values in the game
// These values match the prices array in game.php
$money_values = [
    0.01, 1, 5, 10, 25, 50, 75, 100, 200, 300, 400, 500, 750, 1000, 
    5000, 10000, 25000, 50000, 75000, 100000, 200000, 300000, 400000, 500000, 750000, 1000000
];

/**
 * Initialize a new game
 * Creates a new game state with randomly assigned monetary values to cases
 */
function initialize_game() {
    // Shuffle the money values
    $values = $GLOBALS['money_values'];
    shuffle($values);
    
    // Create an array to hold the cases
    $cases = [];
    
    // Assign money values to cases (1-26)
    for ($i = 1; $i <= 26; $i++) {
        $cases[$i] = $values[$i - 1];
    }
    
    // Initialize game state
    $_SESSION['game'] = [
        'cases' => $cases,
        'remaining_cases' => range(1, 26),
        'opened_cases' => [],
        'player_case' => null,
        'round' => 0,
        'cases_to_open' => [6, 5, 4, 3, 2, 1, 1, 1, 1, 1], // Number of cases to open in each round
        'current_round_cases_opened' => 0,
        'banker_offers' => [],
        'game_finished' => false,
        'final_winnings' => null,
        'game_start_time' => time(),
        'last_action_time' => time()
    ];
    
    return $_SESSION['game'];
}

/**
 * Choose player's case
 * The player selects their personal case to keep until the end
 */
function choose_player_case($case_number) {
    if (!isset($_SESSION['game']) || $_SESSION['game']['player_case'] !== null) {
        return false; // Game not initialized or player case already chosen
    }
    
    if (!in_array($case_number, $_SESSION['game']['remaining_cases'])) {
        return false; // Case is not available
    }
    
    $_SESSION['game']['player_case'] = $case_number;
    $_SESSION['game']['last_action_time'] = time();
    
    return true;
}

/**
 * Open a case to reveal its value
 */
function open_case($case_number) {
    if (!isset($_SESSION['game']) || $_SESSION['game']['player_case'] === null) {
        return false; // Game not initialized or player case not chosen
    }
    
    if (!in_array($case_number, $_SESSION['game']['remaining_cases']) || 
        $case_number == $_SESSION['game']['player_case']) {
        return false; // Case is not available or it's the player's case
    }
    
    // Get the value of the case
    $value = $_SESSION['game']['cases'][$case_number];
    
    // Move the case from remaining to opened
    $remaining_key = array_search($case_number, $_SESSION['game']['remaining_cases']);
    unset($_SESSION['game']['remaining_cases'][$remaining_key]);
    $_SESSION['game']['remaining_cases'] = array_values($_SESSION['game']['remaining_cases']); // Re-index array
    
    // Add to opened cases
    $_SESSION['game']['opened_cases'][$case_number] = $value;
    
    // Increment the counter for cases opened this round
    $_SESSION['game']['current_round_cases_opened']++;
    
    // Check if we've opened all cases for this round
    if ($_SESSION['game']['current_round_cases_opened'] >= $_SESSION['game']['cases_to_open'][$_SESSION['game']['round']]) {
        // Move to banker offer phase
        $_SESSION['game']['current_round_cases_opened'] = 0;
        $_SESSION['game']['last_action_time'] = time();
        
        // Generate banker's offer
        $banker_offer = calculate_banker_offer();
        $_SESSION['game']['banker_offers'][] = $banker_offer;
        
        return [
            'value' => $value,
            'banker_offer' => $banker_offer,
            'end_of_round' => true
        ];
    }
    
    $_SESSION['game']['last_action_time'] = time();
    
    return [
        'value' => $value,
        'end_of_round' => false
    ];
}

/**
 * Calculate the banker's offer based on remaining cases
 */
function calculate_banker_offer() {
    $remaining_values = [];
    foreach ($_SESSION['game']['remaining_cases'] as $case) {
        if ($case != $_SESSION['game']['player_case']) {
            $remaining_values[] = $_SESSION['game']['cases'][$case];
        }
    }
    
    // Add the player's case value
    $remaining_values[] = $_SESSION['game']['cases'][$_SESSION['game']['player_case']];
    
    // Calculate expected value (average of remaining values)
    $expected_value = array_sum($remaining_values) / count($remaining_values);
    
    // Banker is stingier in early rounds, more generous in later rounds
    $round_factor = 0.5 + ($_SESSION['game']['round'] * 0.05); // Increases with each round
    
    // Cap the factor at 0.95 (banker will never offer full expected value)
    $round_factor = min($round_factor, 0.95);
    
    // Calculate banker's offer
    $offer = round($expected_value * $round_factor);
    
    // Add some randomness (-5% to +5%)
    $randomness = (mt_rand(95, 105) / 100);
    $offer = round($offer * $randomness);
    
    return $offer;
}

/**
 * Handle the player's decision on the banker's offer
 */
function handle_banker_offer($decision) {
    if (!isset($_SESSION['game']) || empty($_SESSION['game']['banker_offers'])) {
        return false; // Game not initialized or no banker offers
    }
    
    $current_offer = end($_SESSION['game']['banker_offers']);
    
    if ($decision === 'deal') {
        // Player accepts the offer
        $_SESSION['game']['game_finished'] = true;
        $_SESSION['game']['final_winnings'] = $current_offer;
        save_game_results($_SESSION['game']['final_winnings']);
        return [
            'accepted' => true,
            'amount' => $current_offer
        ];
    } else {
        // Player declines the offer
        $_SESSION['game']['round']++;
        
        // Check if this was the last round
        if ($_SESSION['game']['round'] >= count($_SESSION['game']['cases_to_open']) ||
            count($_SESSION['game']['remaining_cases']) <= 2) { // Player's case + 1 other case
            
            // Final round - force the player to choose between their case and the last remaining case
            if (count($_SESSION['game']['remaining_cases']) === 2) {
                return [
                    'accepted' => false,
                    'final_round' => true,
                    'remaining_cases' => $_SESSION['game']['remaining_cases']
                ];
            } else {
                // Game ends with the player's case
                $player_case_value = $_SESSION['game']['cases'][$_SESSION['game']['player_case']];
                $_SESSION['game']['game_finished'] = true;
                $_SESSION['game']['final_winnings'] = $player_case_value;
                save_game_results($_SESSION['game']['final_winnings']);
                return [
                    'accepted' => false,
                    'keep_case' => true,
                    'player_case' => $_SESSION['game']['player_case'],
                    'amount' => $player_case_value
                ];
            }
        }
        
        return [
            'accepted' => false,
            'next_round' => $_SESSION['game']['round'],
            'cases_to_open' => $_SESSION['game']['cases_to_open'][$_SESSION['game']['round']]
        ];
    }
}

/**
 * Switch cases at the end (if only 2 cases remain)
 */
function switch_case($switch) {
    if (!isset($_SESSION['game']) || count($_SESSION['game']['remaining_cases']) !== 2) {
        return false;
    }
    
    // Find the other case (not the player's case)
    $other_case = null;
    foreach ($_SESSION['game']['remaining_cases'] as $case) {
        if ($case != $_SESSION['game']['player_case']) {
            $other_case = $case;
            break;
        }
    }
    
    if ($switch) {
        // Player switches to the other case
        $final_case = $other_case;
    } else {
        // Player keeps their original case
        $final_case = $_SESSION['game']['player_case'];
    }
    
    $final_value = $_SESSION['game']['cases'][$final_case];
    $_SESSION['game']['game_finished'] = true;
    $_SESSION['game']['final_winnings'] = $final_value;
    
    save_game_results($_SESSION['game']['final_winnings']);
    
    return [
        'case' => $final_case,
        'amount' => $final_value
    ];
}

/**
 * Save game results to cookies for leaderboard
 */
function save_game_results($winnings) {
    // Get player name from session
    $player_name = isset($_SESSION['player_name']) ? $_SESSION['player_name'] : 'Anonymous';
    
    // Set cookies for the leaderboard.php to use
    setcookie('name', $player_name, time() + 86400 * 30); // Cookie expires in 30 days
    setcookie('earnings', $winnings, time() + 86400 * 30); // Cookie expires in 30 days
    
    return true;
}

/**
 * Get current game state
 */
function get_game_state() {
    if (!isset($_SESSION['game'])) {
        return null;
    }
    
    return $_SESSION['game'];
}

/**
 * Reset the game
 */
function reset_game() {
    unset($_SESSION['game']);
    return true;
}

/**
 * Format money value for display
 */
function format_money($amount) {
    if ($amount < 1) {
        // For cents (0.01)
        return '$' . number_format($amount, 2);
    } else {
        // For dollar amounts
        return '$' . number_format($amount, 0);
    }
}

/**
 * Get list of all money values and their status (removed or available)
 */
function get_money_board_status() {
    if (!isset($_SESSION['game'])) {
        return null;
    }
    
    $money_status = [];
    foreach ($GLOBALS['money_values'] as $value) {
        $found = false;
        foreach ($_SESSION['game']['opened_cases'] as $case_value) {
            if ($case_value == $value) {
                $found = true;
                break;
            }
        }
        $money_status[$value] = $found ? 'removed' : 'available';
    }
    
    return $money_status;
}

// Process game actions if this file is accessed directly via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    $response = ['success' => false];
    
    switch ($action) {
        case 'init':
            $response['game'] = initialize_game();
            $response['success'] = true;
            break;
            
        case 'choose_case':
            $case_number = isset($_POST['case']) ? (int)$_POST['case'] : 0;
            $response['success'] = choose_player_case($case_number);
            if ($response['success']) {
                $response['game'] = get_game_state();
            }
            break;
            
        case 'open_case':
            $case_number = isset($_POST['case']) ? (int)$_POST['case'] : 0;
            $result = open_case($case_number);
            if ($result) {
                $response['success'] = true;
                $response['result'] = $result;
                $response['game'] = get_game_state();
            }
            break;
            
        case 'banker_decision':
            $decision = isset($_POST['decision']) ? $_POST['decision'] : null;
            $result = handle_banker_offer($decision);
            if ($result) {
                $response['success'] = true;
                $response['result'] = $result;
                $response['game'] = get_game_state();
            }
            break;
            
        case 'switch_case':
            $switch = isset($_POST['switch']) ? (bool)$_POST['switch'] : false;
            $result = switch_case($switch);
            if ($result) {
                $response['success'] = true;
                $response['result'] = $result;
                $response['game'] = get_game_state();
            }
            break;
            
        case 'reset':
            $response['success'] = reset_game();
            break;
            
        case 'get_state':
            $response['game'] = get_game_state();
            $response['success'] = true;
            break;
            
        case 'set_player_name':
            $player_name = isset($_POST['name']) ? $_POST['name'] : 'Anonymous';
            $_SESSION['player_name'] = $player_name;
            $response['success'] = true;
            break;
            
        case 'end_game':
            // If the game is over, redirect to leaderboard
            $winnings = isset($_SESSION['game']['final_winnings']) ? $_SESSION['game']['final_winnings'] : 0;
            save_game_results($winnings);
            $response['success'] = true;
            $response['redirect'] = 'leaderboard.php';
            break;
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Initialize game if needed and this is included directly
if (!isset($_SESSION['game']) && !isset($_POST['action'])) {
    initialize_game();
}
?>