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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Magkano Ang Pao Mo?</title>
</head>
<body>
    <div>
        <h1>Magkano Ang Pao Mo?</h1>
        
        <?php
        // Display current step
        $step = isset($_SESSION['step']) ? $_SESSION['step'] : 1;
        
        // STEP 1: Ask how many Ang Paos received
        if ($step == 1) {
            ?>
            <h2>Algorithm 1</h2> 
            <h3>Ang Pao Count</h3> 
            <form method="POST" action="">
                <label>How many Ang Pao's did you receive? (1-10): </label>
                <input type="number" name="numberOfAngPao" min="1" max="10" required>
                <input type="submit" name="submit_angpao" value="Next">
            </form>
            <?php
        }
        
        // STEP 1b: Enter values of each Ang Pao
        elseif ($step == 2) {
            // Check if numberOfAngPao is set
            if (!isset($_SESSION['numberOfAngPao'])) {
                $_SESSION['step'] = 1;
                echo "<p>Session error. Please start over.</p>";
                echo "<form method='POST' action=''><input type='submit' name='reset' value='Start Over'></form>";
            } else {
                $count = $_SESSION['numberOfAngPao'];
                ?>
                <h2>Algorithm 2</h2>
                <h3>Enter the value of each Ang Pao</h3>
                <form method="POST" action="">
                    <?php
                    for ($i = 1; $i <= $count; $i++) {
                        echo "<label>Ang Pao #$i: PHP</label>";
                        echo "<input type='number' name='angpao_$i' step='0.01' min='1' required><br>";
                    }
                    ?>
                    <br>
                    <input type="submit" name="submit_angpao_values" value="Calculate Total">
                </form>
                <?php
            }
        }
        
        // STEP 2: Expenses Section
        elseif ($step == 3) {
            ?>
            <h2>Algorithm 3</h2>
            <h3>Expenses & Details</h3>
            <form method="POST" action="">
                <h3>Expenses:</h3>
                <label>Food Expenses (PHP): </label>
                <input type="number" name="foodExpenses" step="0.01" min="0" required><br>
                
                <label>Transportation Expenses (PHP): </label>
                <input type="number" name="transportationExpense" step="0.01" min="0" required><br>
                
                <h3>Lucky Details:</h3>
                <label>Lucky Number (1-99): </label>
                <input type="number" name="luckyNumber" min="1" max="99" required><br>
                
                <label>Birth Year Animal: </label>
                <select name="birthYearAnimal" required>
                    <option value="">Select Animal</option>
                    <option value="Rat">Rat</option>
                    <option value="Ox">Ox</option>
                    <option value="Tiger">Tiger</option>
                    <option value="Rabbit">Rabbit</option>
                    <option value="Dragon">Dragon</option>
                    <option value="Snake">Snake</option>
                    <option value="Horse">Horse</option>
                    <option value="Goat">Goat</option>
                    <option value="Monkey">Monkey</option>
                    <option value="Rooster">Rooster</option>
                    <option value="Dog">Dog</option>
                    <option value="Pig">Pig</option>
                </select><br>
                
                <label>Color of Underwear: </label>
                <select name="colorOfUnderware" required>
                    <option value="">Select Color</option>
                    <option value="Red">Red</option>
                    <option value="Black">Black</option>
                    <option value="Blue">Blue</option>
                    <option value="Green">Green</option>
                    <option value="Yellow">Yellow</option>
                    <option value="Purple">Purple</option>
                    <option value="Orange">Orange</option>
                    <option value="Pink">Pink</option>
                    <option value="Brown">Brown</option>
                    <option value="White">White</option>
                </select><br><br>
                
                <input type="submit" name="submit_expenses" value="Calculate Lucky Status">
            </form>
            <?php
        }
        
        // STEP 3: Summary Section
        elseif ($step == 4) {
            // Check if all required session variables exist
            if (!isset($_SESSION['angPaoValues']) || empty($_SESSION['angPaoValues']) || 
                !isset($_SESSION['foodExpenses']) || !isset($_SESSION['transportationExpense']) || 
                !isset($_SESSION['luckyNumber']) || !isset($_SESSION['birthYearAnimal']) || 
                !isset($_SESSION['colorOfUnderware'])) {
                $_SESSION['step'] = 1;
                echo "<p>Session expired. Please start over.</p>";
                echo "<form method='POST' action=''><input type='submit' name='reset' value='Start Over'></form>";
            } else {
                $totalAngPao = array_sum($_SESSION['angPaoValues']);
                $foodExpenses = $_SESSION['foodExpenses'];
                $transpoExpenses = $_SESSION['transportationExpense'];
                $luckyNumber = $_SESSION['luckyNumber'];
                $birthYearAnimal = $_SESSION['birthYearAnimal'];
                $colorOfUnderware = $_SESSION['colorOfUnderware'];
                
                // Calculate total expenses
                $totalExpenses = $foodExpenses + $transpoExpenses;
                $remaining = $totalAngPao - $totalExpenses + 500;
                ?>
                <h2>Step 4</h2>
                <h3>Summary/Confirmation</h3>

                <h3>Ang Pao Summary:</h3>
                <p>Number of Ang Pao: <?php echo count($_SESSION['angPaoValues']); ?></p>
                <p>Individual Ang Pao Values: <?php echo implode(", ", $_SESSION['angPaoValues']); ?></p>
                <p><strong>Total Ang Pao: PHP<?php echo number_format($totalAngPao, 2); ?></strong></p>
                
                <h3>Expenses Summary:</h3>
                <p>Food Expenses: PHP<?php echo number_format($foodExpenses, 2); ?></p>
                <p>Transportation Expenses: PHP<?php echo number_format($transpoExpenses, 2); ?></p>
                <p>Total Expenses: PHP<?php echo number_format($totalExpenses, 2); ?></p>
                
                <h3>Other Details:</h3>
                <p>Lucky Number: <?php echo $luckyNumber; ?></p>
                <p>Birth Year Animal: <?php echo $birthYearAnimal; ?></p>
                <p>Underwear Color: <?php echo $colorOfUnderware; ?></p>
                <p>Fixed Bonus: PHP500.00</p>
                
                <h3>Calculations:</h3>
                <p>Remaining Money (after expenses + bonus): PHP<?php echo number_format($remaining, 2); ?></p>
                
                <!-- Go to Step 5: Lucky Status-->
                <form method="POST" action="" style="display: inline;">
                    <input type="submit" name="confirm" value="Confirm & See Lucky Status">
                </form>
                
                <!--Go to Step 2: Edit Ang Pao values  -->
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="edit_step" value="2">
                    <input type="submit" name="edit" value="Edit Ang Pao Values">
                </form>
                
                <!--  Go to Step 3: Edit Expenses-->
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="edit_step" value="3">
                    <input type="submit" name="edit" value="Edit Expenses">
                </form>

                <!--Go to Step 1: Reset All Data -->
                <form method="POST" action="" style="display: inline;">
                    <input type="submit" name="reset" value="Reset All Data">
                </form>
                <?php
            }
        }
        
        // STEP 5: Lucky Section
        elseif ($step == 5) {
            // Check if all required session variables exist
            if (!isset($_SESSION['angPaoValues']) || empty($_SESSION['angPaoValues']) || 
                !isset($_SESSION['foodExpenses']) || !isset($_SESSION['transportationExpense']) || 
                !isset($_SESSION['luckyNumber']) || !isset($_SESSION['birthYearAnimal']) || 
                !isset($_SESSION['colorOfUnderware'])) {
                $_SESSION['step'] = 1;
                echo "<p>Session expired. Please start over.</p>";
                echo "<form method='POST' action=''><input type='submit' name='reset' value='Start Over'></form>";
            } else {
                $totalAngPao = array_sum($_SESSION['angPaoValues']);
                $foodExpenses = $_SESSION['foodExpenses'];
                $transpoExpenses = $_SESSION['transportationExpense'];
                $luckyNumber = $_SESSION['luckyNumber'];
                $birthYearAnimal = $_SESSION['birthYearAnimal'];
                $colorOfUnderware = $_SESSION['colorOfUnderware'];
                
                $result = calculateLuckyStatus(
                    $totalAngPao, 
                    $foodExpenses, 
                    $transpoExpenses, 
                    $luckyNumber, 
                    $birthYearAnimal,
                    $colorOfUnderware
                );
                
                echo "<h2>Your Lucky Status</h2>";
                echo "<h1>" . $result['status'] . "</h1>";
                
                // Display bonuses
                if ($result['horseBonusApplied']) {
                    echo "<p style='color: green;'>Horse Bonus Applied! (Remaining money doubled)</p>";
                }
                if ($result['redBonusApplied']) {
                    echo "<p style='color: red;'>Red Underwear Bonus! 75% discount applied!</p>";
                }
                
                echo "<p>Total Ang Pao: PHP" . number_format($result['totalAngPao'], 2) . "</p>";
                echo "<p>Total Expenses: PHP" . number_format($result['totalExpenses'], 2) . "</p>";
                echo "<p>Remaining Money: PHP" . number_format($result['remainingMoney'], 2) . "</p>";
                
                // Comparison results
                if ($result['isRemainingGreaterThan5000']) {
                    echo "<p>✓ Remaining money is greater than PHP5000</p>";
                }
                if ($result['isRemainingEqualTo8']) {
                    echo "<p>✨ Lucky number 8 detected!</p>";
                }
                if ($result['isTotalExpensesGreaterThanTotalAngPao']) {
                    echo "<p>⚠️ Total expenses exceeded total Ang Pao</p>";
                }
                
                echo "<h2>Special Promo!</h2>";
                echo "<p>You get <strong>" . $result['discount'] . "% discount</strong> on your next purchase!</p>";
                
                echo "<br><form method='POST' action=''>";
                echo "<input type='submit' name='reset' value='Start New Session'>";
                echo "</form>";
                
                // AUTO-RESET CODE
                echo "<meta http-equiv='refresh' content='10;url=" . $_SERVER['PHP_SELF'] . "?reset=true'>";
                echo "<p><small>Page will reset automatically in 10 seconds...</small></p>";
            }
        } else {
            // Fallback - if step is not 1-5, set to 1
            $_SESSION['step'] = 1;
            echo "<p>Redirecting to start...</p>";
            echo "<meta http-equiv='refresh' content='1;url=" . $_SERVER['PHP_SELF'] . "'>";
        }
        ?>
        
    </div>
</body>
</html>