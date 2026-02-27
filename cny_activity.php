<?php include_once("script.php") ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Magkano Ang Pao Mo?</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Magkano Ang Pao Mo?</h1>
        
        <?php
        // Display current step
        $step = isset($_SESSION['step']) ? $_SESSION['step'] : 1;
        
        // STEP 1: Combined Ang Pao Count + Value Entry
        if ($step == 1) {
            // Initialize count if not set
            if (!isset($_SESSION['angpao_count'])) {
                $_SESSION['angpao_count'] = 1;
            }
            
            // Handle increment
            if (isset($_POST['increment'])) {
                if ($_SESSION['angpao_count'] < 20) {
                    $_SESSION['angpao_count']++;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            
            // Handle decrement
            if (isset($_POST['decrement'])) {
                if ($_SESSION['angpao_count'] > 1) {
                    $_SESSION['angpao_count']--;
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            
            // Handle save and proceed
            if (isset($_POST['save_and_proceed'])) {
                $angPaoValues = array();
                $valid = true;
                
                for ($i = 1; $i <= $_SESSION['angpao_count']; $i++) {
                    if (isset($_POST['angpao_' . $i]) && is_numeric($_POST['angpao_' . $i]) && $_POST['angpao_' . $i] > 0) {
                        $angPaoValues[] = $_POST['angpao_' . $i];
                    } else {
                        $valid = false;
                        echo "<p style='color: red;'>Error: Please enter valid amount for Ang Pao #$i</p>";
                    }
                }
                
                if ($valid) {
                    $_SESSION['numberOfAngPao'] = $_SESSION['angpao_count'];
                    $_SESSION['angPaoValues'] = $angPaoValues;
                    $_SESSION['step'] = 3; // Go to expenses
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            }
            
            $count = $_SESSION['angpao_count'];
            ?>
            
            <div class="counter-container">
                <h2>Algorithm 1</h2>
                <h3>Ang Pao Count & Values</h3>
                
                <div class="counter-controls">
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="decrement" class="btn-counter" 
                            <?php echo ($count <= 1) ? 'disabled' : ''; ?>>−</button>
                    </form>
                    
                    <span class="count-display"><?php echo $count; ?></span>
                    
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="increment" class="btn-counter"
                            <?php echo ($count >= 20) ? 'disabled' : ''; ?>>+</button>
                    </form>
                </div>
                
                <p class="counter-label">Number of Ang Pao's: <strong><?php echo $count; ?></strong></p>
                
                <form method="POST" action="" class="values-form">
                    <h4>Enter amount for each Ang Pao:</h4>
                    
                    <?php
                    for ($i = 1; $i <= $count; $i++) {
                        $value = isset($_SESSION['temp_values'][$i]) ? $_SESSION['temp_values'][$i] : '';
                        echo "<div class='input-group'>";
                        echo "<label>Ang Pao #$i: PHP</label>";
                        echo "<input type='number' name='angpao_$i' step='0.01' min='1' value='$value' required>";
                        echo "</div>";
                    }
                    ?>
                    
                    <br>
                    <button type="submit" name="save_and_proceed" class="btn-proceed">Save & Proceed to Expenses →</button>
                </form>
            </div>
            <?php
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
            
            <form method="POST" action="" style="display:inline;">
                <button type="submit" name="back_to_step1" class="btn-proceed">← Back to Edit Ang Pao</button>
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
                
                <!-- Go to Step 3: Edit Expenses-->
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="edit_step" value="3">
                    <input type="submit" name="edit" value="Edit Expenses">
                </form>

                <!--Go to Step 1: Edit Ang Pao -->
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="edit_step" value="1">
                    <input type="submit" name="edit" value="Edit Ang Pao">
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
                if ($result['luckyNumberBonusApplied']) {
                    echo "<p style='color: green;'>Lucky Number 8 Bonus Applied! (Remaining money doubled)</p>";
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
                    echo "<p>Lucky number 8 detected!</p>";
                }
                if ($result['isTotalExpensesGreaterThanTotalAngPao']) {
                    echo "<p>Total expenses exceeded total Ang Pao</p>";
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
        }
        ?>
        
    </div>
</body>
</html>