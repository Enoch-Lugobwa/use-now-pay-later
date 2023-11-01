<?php
// Function to check subscription status
// Include your database connection script and any necessary functions
require_once "db_connection.php";
function checkSubscriptionStatus($conn, $phoneNumber) {
    $stmt = $conn->prepare("SELECT is_subscribed FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($isSubscribed);
    $stmt->fetch();
    $stmt->close();
    return $isSubscribed == 1;
}

// Function to check if a user exists in the database
function checkUserExistence($conn, $phoneNumber) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Function to subscribe the user
function subscribeUser($conn, $phoneNumber) {
    $defaultPin = "1234"; // Default PIN
    
    if (checkUserExistence($conn, $phoneNumber)) {
        // User already exists, update subscription status and default PIN
        $stmt = $conn->prepare("UPDATE users SET is_subscribed = 1, pin = ? WHERE phone_number = ?");
        $stmt->bind_param("ss", $defaultPin, $phoneNumber);
    } else {
        // User doesn't exist, insert a new record with subscription status and default PIN
        $stmt = $conn->prepare("INSERT INTO users (phone_number, is_subscribed, loan_amount, eligibility, pin) VALUES (?, 1, 0, 150000, ?)");
        $stmt->bind_param("ss", $phoneNumber, $defaultPin);
    }
    $stmt->execute();
    $stmt->close();
}


// Modify the subscribeNewUser function to use the subscribeUser function
function subscribeNewUser($conn, $phoneNumber) {
    subscribeUser($conn, $phoneNumber);
}


// Function to fetch eligibility amount
function fetchEligibility($conn, $phoneNumber) {
    $stmt = $conn->prepare("SELECT eligibility FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($eligibility);
    $stmt->fetch();
    $stmt->close();
    return $eligibility;
}
// Function to fetch loan amount from the database
function fetchLoanAmount($conn, $phoneNumber) {
    $stmt = $conn->prepare("SELECT loan_amount FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($loanAmount);
    $stmt->fetch();
    $stmt->close();
    return $loanAmount;
}

// Function to deduct the full loan amount
function deductFullLoanAmount($conn, $phoneNumber, $loanAmount) {
    // Implement the logic to deduct the full loan amount from the user's account
    // This may involve updating the database and performing financial transactions
    // Return true if deduction is successful, false otherwise
}

// Function to make partial loan repayment
function makePartialRepayment($conn, $phoneNumber, $partialRepaymentAmount) {
    // Implement the logic to make a partial loan repayment
    // This may involve updating the database and performing financial transactions
    // Return true if the partial repayment is successful, false otherwise
}
// Function to unsubscribe the user
function unsubscribeUser($conn, $phoneNumber) {
    $stmt = $conn->prepare("UPDATE users SET is_subscribed = 0 WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->close();
}
// Function to verify PIN against the database PIN
function verifyPin($conn, $phoneNumber, $pin) {
    $stmt = $conn->prepare("SELECT pin FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($dbPin);
    $stmt->fetch();
    $stmt->close();

    // Verify the entered PIN against the database PIN
    return ($pin === $dbPin);
}

function generateMainMenu() {
    $response  = "CON Main Menu\n";
    $response .= "1. Check Eligibility\n";
    $response .= "2. Request Service\n";
    $response .= "3. Repay Service Debt\n";
    $response .= "4. Unsubscribe\n";
    return $response;
}


function generatePinError() {
    $response = "END Invalid PIN. Please try again.\n";
    return $response;
}

// Function to generate the welcome menu and handle PIN entry
function generateWelcomeMenu($conn, $phoneNumber, $text) {
    $response = "";

    // Check if the user is subscribed
    $subscriptionStatus = checkSubscriptionStatus($conn, $phoneNumber);

    if (!$subscriptionStatus) {
        // If the user is not subscribed, request the PIN entry
        $response = "CON Use Now Pay Later.\n";
        $response .= "Please read Terms and Conditions at detela.co.ug/ussd before you proceed.\n";
        $response .= "Enter Pin to confirm.\n";
    } else {
        // If the user is subscribed, show the main menu and continue the session
        $response = generateMainMenu();
    }

    

    return $response;
}
// Function to fetch the user's PIN from the database
function fetchUserPin($conn, $phoneNumber) {
    $pin = "";

    // Prepare and execute a query to fetch the user's PIN
    $stmt = $conn->prepare("SELECT pin FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->bind_result($pin);
    $stmt->fetch();
    $stmt->close();

    return $pin;
}




