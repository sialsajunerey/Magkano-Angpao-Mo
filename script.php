<?php
session_start();

// IMPORTANT: Handle reset via GET parameter FIRST (for auto-reset)
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    session_destroy();
    session_start();
    $_SESSION['step'] = 1; // Changed to numeric 1
    $_SESSION['angPaoValues'] = array();
    // Redirect to remove the reset parameter from URL
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Initialize variables
$numberOfAngPao = 0;
$totalAngPao = 0;
$foodExpenses = 0;
$transportationExpense = 0;
$totalExpenses = 0;
$remainingMoney = 0;
$fixedBonus = 500;
$luckyNumber = 0;
$birthYearAnimal = "";
$colorOfUnderware = "";
$angPaoArray = array();
$luckyStatus = "";
$discountPercentage = 0;

// Initialize session for multi-step form - use NUMERIC steps
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; 
    $_SESSION['angPaoValues'] = array();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Step 1: Ang Pao Section
    if (isset($_POST['submit_angpao'])) {
        if (isset($_POST['numberOfAngPao']) && is_numeric($_POST['numberOfAngPao'])) {
            $_SESSION['numberOfAngPao'] = (int)$_POST['numberOfAngPao'];
            $_SESSION['step'] = 2; 
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Step 1b: Submit individual Ang Pao values
    if (isset($_POST['submit_angpao_values'])) {
        $angPaoValues = array();
        $valid = true;
        for ($i = 1; $i <= $_SESSION['numberOfAngPao']; $i++) {
            if (isset($_POST['angpao_' . $i])) {
                $value = $_POST['angpao_' . $i];
                // Input validation: is number and > 0
                if (is_numeric($value) && $value > 0) {
                    $angPaoValues[] = $value;
                } else {
                    $valid = false;
                    echo "<p style='color: red;'>Error: Ang Pao value must be a positive number!</p>";
                }
            }
        }
        if ($valid) {
            $_SESSION['angPaoValues'] = $angPaoValues;
            $_SESSION['step'] = 3;  

            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Step 2: Expenses Section
    if (isset($_POST['submit_expenses'])) {
        $_SESSION['foodExpenses'] = $_POST['foodExpenses'];
        $_SESSION['transportationExpense'] = $_POST['transportationExpense'];
        $_SESSION['luckyNumber'] = $_POST['luckyNumber'];
        $_SESSION['birthYearAnimal'] = $_POST['birthYearAnimal'];
        $_SESSION['colorOfUnderware'] = $_POST['colorOfUnderware'];
        $_SESSION['step'] = 4; // Changed to numeric 4
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Step 3: Confirm/Edit
    if (isset($_POST['confirm'])) {
        $_SESSION['step'] = 5; 
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['edit'])) {
        $_SESSION['step'] = (int)$_POST['edit_step']; 
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Reset via POST
    if (isset($_POST['reset'])) {
        session_destroy();
        session_start();
        $_SESSION['step'] = 1; // Changed to numeric 1
        $_SESSION['angPaoValues'] = array();
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Calculate function
function calculateLuckyStatus($totalAngPao, $foodExpenses, $transportationExpense, $luckyNumber, $birthYearAnimal, $colorOfUnderware) {
    
    // Arithmetic Operations
    $totalExpenses = $foodExpenses + $transportationExpense;
    $remainingMoney = $totalAngPao - $foodExpenses;
    
    // Assignment Operations
    $remainingMoney -= $transportationExpense;
    $remainingMoney += 500; // Fixed Bonus
    
    // Horse bonus (multiply by 2 if horse)
    $horseBonusApplied = false;
    if ($birthYearAnimal == "Horse") {
        $remainingMoney = $remainingMoney * 2;
        $horseBonusApplied = true;
    }
    
    // Comparison Operations
    $isRemainingGreaterThan5000 = $remainingMoney > 5000;
    $isRemainingEqualTo8 = $remainingMoney == 8;
    $isTotalExpensesGreaterThanTotalAngPao = $totalExpenses > $totalAngPao;
    
    // Logical Operations - Determine Lucky Status
    $status = "";
    $discount = 0;
    
    if ($isTotalExpensesGreaterThanTotalAngPao) {
        $status = "Unfortunately Unlucky";
        $discount = 5;
    } elseif ($totalAngPao < 5000) {
        $status = "Unlucky";
        $discount = 10;
    } elseif ($totalAngPao > 5000) {
        $status = "Lucky";
        $discount = 25;
    }
    
    // Extremely Lucky condition
    if ($totalAngPao > 5000 && $birthYearAnimal == "Horse" && $luckyNumber == 8) {
        $status = "Extremely Lucky";
        $discount = 50;
    }
    
    // Red underwear gives 75% off regardless of status
    $redBonusApplied = false;
    if ($colorOfUnderware == "Red") {
        $discount = 75;
        $redBonusApplied = true;
    }
    
    return array(
        'status' => $status,
        'discount' => $discount,
        'remainingMoney' => $remainingMoney,
        'totalExpenses' => $totalExpenses,
        'totalAngPao' => $totalAngPao,
        'horseBonusApplied' => $horseBonusApplied,
        'redBonusApplied' => $redBonusApplied,
        'isRemainingGreaterThan5000' => $isRemainingGreaterThan5000,
        'isRemainingEqualTo8' => $isRemainingEqualTo8,
        'isTotalExpensesGreaterThanTotalAngPao' => $isTotalExpensesGreaterThanTotalAngPao
    );
}
?>