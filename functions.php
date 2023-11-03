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
    
    if (checkUserExistence($conn, $phoneNumber)) {
        // User already exists, update subscription status and default PIN
        $stmt = $conn->prepare("UPDATE users SET is_subscribed = 1 WHERE phone_number = ?");
        $stmt->bind_param("s", $phoneNumber);
    } else {
        // User doesn't exist, insert a new record with subscription status and default PIN
        $stmt = $conn->prepare("INSERT INTO users (phone_number, is_subscribed, loan_amount, eligibility) VALUES (?, 1, 0, 150000)");
        $stmt->bind_param("s", $phoneNumber);
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

// Function to perform a full loan payment
function fullLoanPayment($conn, $phoneNumber, $amountPaid) {
    // Get the current loan amount and eligibility amount
    $currentLoanAmount = fetchLoanAmount($conn, $phoneNumber);
    $currentEligibility = fetchEligibility($conn, $phoneNumber);

    // Calculate the new loan amount after deduction
    $newLoanAmount = $currentLoanAmount - $amountPaid;

    // Calculate the new eligibility amount after increment
    $newEligibility = $currentEligibility + $amountPaid;

    // Check if the new loan amount is zero or negative
    if ($newLoanAmount <= 0) {
        // If the loan is fully paid, set loan amount to 0
        $newLoanAmount = 0;

    }

    // Update the loan amount and eligibility in the database
    $stmt = $conn->prepare("UPDATE users SET loan_amount = ?, eligibility = ? WHERE phone_number = ?");
    $stmt->bind_param("dds", $newLoanAmount, $newEligibility, $phoneNumber);
    $stmt->execute();
    $stmt->close();

    // Return true to indicate a successful full loan payment
    return true;
}


// Function to make a partial loan repayment
function makePartialRepayment($conn, $phoneNumber, $partialRepaymentAmount) {
    // Get the current loan amount
    $currentLoanAmount = fetchLoanAmount($conn, $phoneNumber);

    // Check if the user has an outstanding loan
    if ($currentLoanAmount <= 0) {
        // No outstanding loan, return false to indicate failure
        return false;
    }

    // Calculate the new loan amount after deducting the partial repayment
    $newLoanAmount = $currentLoanAmount - $partialRepaymentAmount;

    // Ensure the new loan amount is not negative
    if ($newLoanAmount < 0) {
        $newLoanAmount = 0;
    }

    // Update the loan amount in the database
    $stmt = $conn->prepare("UPDATE users SET loan_amount = ? WHERE phone_number = ?");
    $stmt->bind_param("ds", $newLoanAmount, $phoneNumber);
    $stmt->execute();
    $stmt->close();

    // Return true to indicate a successful partial repayment
    return true;
}


// Function to unsubscribe the user
function unsubscribeUser($conn, $phoneNumber) {
    $stmt = $conn->prepare("UPDATE users SET is_subscribed = 0 WHERE phone_number = ?");
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $stmt->close();
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
// Function to take a loan and update eligibility
function takeLoan($conn, $phoneNumber, $loanAmount) {
    // Check if the loan amount is greater than 0
    if ($loanAmount > 0) {
        // Implement the logic to process the loan request here
        // You can update the user's loan balance, perform financial transactions, and more
        
        // For example, you can update the user's loan balance in the database
        $stmt = $conn->prepare("UPDATE users SET loan_amount = loan_amount + ? WHERE phone_number = ?");
        $stmt->bind_param("ds", $loanAmount, $phoneNumber);
        $stmt->execute();
        $stmt->close();

        // Update eligibility amount
        $eligibility = fetchEligibility($conn, $phoneNumber); // Get the current eligibility
        $newEligibility = $eligibility - $loanAmount; // Reduce eligibility by the loan amount

        // Ensure the new eligibility is not less than 0
        if ($newEligibility < 0) {
            $newEligibility = 0;
        }

        // Update the eligibility in the database
        $stmt = $conn->prepare("UPDATE users SET eligibility = ? WHERE phone_number = ?");
        $stmt->bind_param("ds", $newEligibility, $phoneNumber);
        $stmt->execute();
        $stmt->close();

        // Return true to indicate a successful loan request
        return true;
    } else {
        // Loan amount is invalid (less than or equal to 0)
        return false;
    }
}












