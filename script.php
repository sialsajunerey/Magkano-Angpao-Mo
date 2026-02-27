<?php
session_start();

// IMPORTANT: Handle reset via GET parameter FIRST (for auto-reset)
if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    session_destroy();
    session_start();
    $_SESSION['step'] = 1;
    $_SESSION['angPaoValues'] = array();
    $_SESSION['angpao_count'] = 1;
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
$birthYear = "";
$colorOfUnderware = "";
$angPaoArray = array();
$luckyStatus = "";
$discountPercentage = 0;

// Initialize session for multi-step form
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; 
    $_SESSION['angPaoValues'] = array();
    $_SESSION['angpao_count'] = 1;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Handle back to step 1
    if (isset($_POST['back_to_step1'])) {
        $_SESSION['step'] = 1;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Step 2: Expenses Section
    if (isset($_POST['submit_expenses'])) {
        $_SESSION['foodExpenses'] = $_POST['foodExpenses'];
        $_SESSION['transportationExpense'] = $_POST['transportationExpense'];
        $_SESSION['luckyNumber'] = $_POST['luckyNumber'];
        $_SESSION['birthYear'] = $_POST['birthYear'];
        $_SESSION['birthYearAnimal'] = getChineseZodiac($_POST['birthYear']);
        $_SESSION['colorOfUnderware'] = $_POST['colorOfUnderware'];
        $_SESSION['step'] = 4;
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // Step 4: Confirm/Edit
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
        $_SESSION['step'] = 1;
        $_SESSION['angPaoValues'] = array();
        $_SESSION['angpao_count'] = 1;
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Function to get Chinese Zodiac from birth year
function getChineseZodiac($year) {
    $zodiac = array(
        'Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Snake', 
        'Horse', 'Goat', 'Monkey', 'Rooster', 'Dog', 'Pig'
    );
    
    // Chinese zodiac cycles every 12 years
    // 2020 was Rat year, 2021 Ox, etc.
    // Formula: (year - 4) % 12 gives index
    $index = ($year - 4) % 12;
    
    // Handle negative indices
    if ($index < 0) {
        $index += 12;
    }
    
    return $zodiac[$index];
}

// Calculate function
function calculateLuckyStatus($totalAngPao, $foodExpenses, $transportationExpense, $luckyNumber, $colorOfUnderware) {
    
    // Arithmetic Operations
    $totalExpenses = $foodExpenses + $transportationExpense;
    $remainingMoney = $totalAngPao - $foodExpenses;
    
    // Assignment Operations
    $remainingMoney -= $transportationExpense;
    $remainingMoney += 500; // Fixed Bonus
    
    // Lucky number 8 bonus
    $luckyNumberBonusApplied = false;
    if ($luckyNumber == 8) {
        $remainingMoney *= 2;
        $luckyNumberBonusApplied = true;
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
        'luckyNumberBonusApplied' => $luckyNumberBonusApplied,
        'redBonusApplied' => $redBonusApplied,
        'isRemainingGreaterThan5000' => $isRemainingGreaterThan5000,
        'isRemainingEqualTo8' => $isRemainingEqualTo8,
        'isTotalExpensesGreaterThanTotalAngPao' => $isTotalExpensesGreaterThanTotalAngPao
    );
}
?>