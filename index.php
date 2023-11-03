<?php
// Read the variables sent via POST from Africa's Talking API
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];

require_once('functions.php');

// Initialize the USSD response
$response = "";

if ($text == "") {
    // Generate the welcome menu and handle PIN entry
    $response = generateWelcomeMenu($conn, $phoneNumber, $text);

} elseif ($text == "0") {
    // Go back to the main menu
    $response = generateMainMenu();

}elseif (is_numeric($text) && strlen($text) == 5) {
    // User entered a 4-digit PIN
    //$dbPin = verifyPin($conn, $phoneNumber, $text);

    //if ($dbPin !== false) {
        // If the entered PIN is correct, show the main menu and subscribe the user
        subscribeNewUser($conn, $phoneNumber);
        $response = generateMainMenu();
    //} else {
        // If the entered PIN is incorrect, show an error message
        //$response = "END Invalid PIN. Please try again.\n";
    //}
}elseif ($text == "0*1") {
    // Check if the user has a valid PIN
    $isValidPin = true;

    // Prompt the user to enter their 4-digit PIN
    $response = "CON Please enter your 4-digit PIN to check eligibility:\n";
    
    // Check if the user entered a valid PIN
    if (is_numeric($text) && strlen($text) == 4) {
        $isValidPin = verifyPin($conn, $phoneNumber, $text);
    }

    if ($isValidPin) {
        // PIN is valid, check eligibility and provide a response
        $eligibility = fetchEligibility($conn, $phoneNumber);
        $response = "CON Your eligibility amount: $eligibility\n";
        $response .= "0. Go back to Main Menu\n";
    }
} 
elseif ($text == "1") {
    // Check if the user has a valid PIN
    $isValidPin = true;

    // Prompt the user to enter their 4-digit PIN
    $response = "CON Please enter your 4-digit PIN to check eligibility:\n";
    
    // Check if the user entered a valid PIN
    if (is_numeric($text) && strlen($text) == 4) {
        $isValidPin = verifyPin($conn, $phoneNumber, $text);
    }

    if ($isValidPin) {
        // PIN is valid, check eligibility and provide a response
        $eligibility = fetchEligibility($conn, $phoneNumber);
        $response = "CON Your eligibility amount: $eligibility\n";
        $response .= "0. Go back to Main Menu\n";
    }
} else if ($text == "2") {
    // Show Service request categories
    $response  = "CON Select Service Category\n";
    $response .= "1. Utilities\n";
    $response .= "2. Pay TV\n";
    $response .= "3. Gas\n";
} // Add Utilities submenu
else if ($text == "2*1") {
    // Show Utilities options
    $response  = "CON Select Utility Provider\n";
    $response .= "1. Umeme\n";
    $response .= "2. NWSC\n";
}else if ($text == "2*1*1") {
    // Show Utilities options
    $response  = "CON Select Utility Provider\n";
    $response .= "1. Pay Yaka\n";
    $response .= "2. Pay Bill\n";
}
else if ($text == "2*1*2") {
    // Show Utilities options
    $response  = "CON Select Utility Provider\n";
    $response .= "1. Pay Bill\n";
    
}
// Add Pay TV and Gas menus
else if ($text == "2*2") {
    // Show Pay TV options
    $response  = "CON Select Pay TV Provider\n";
    $response .= "1. DSTV\n";
    $response .= "2. GOtv\n";
    $response .= "3. Azam TV\n";
    $response .= "4. Star Times\n";
    $reponsse .= "5. YOTV\n";
}

else if ($text == "2*3") {
    // Show Gas service providers or options
    $response  = "CON Select Gas Provider\n";
    $response .= "1. Shell\n";
    $response .= "2. Total\n";
    $response .= "3. Moga\n";

}else if (strpos($text, "2*1*1*1") === 0) { // User selected "Utilities" and "Umeme" and entered account number
    $accountNumber = substr($text, 8); // Extract the account number
    if ($accountNumber != "") {
        // Prompt the user to enter the loan amount
        $response = "CON Enter the loan amount to credit to account $accountNumber:\n";
    } else {
        // Invalid account number, ask the user to enter it again
        $response = "CON Please enter the account number:\n";
    }
} elseif (strpos($text, "2*1*1*1*") === 0) { // User entered the loan amount
    $accountLoan = substr($text, 6); // Extract the account number and loan amount together
    list($accountNumber, $loanAmount) = explode("*", $accountLoan);

    // Make sure to validate $accountNumber and $loanAmount as needed here

    if ($accountNumber != "" && is_numeric($loanAmount) && (float)$loanAmount > 0) {
        // Check if the loan amount is valid and the user is eligible

        // You should implement logic to check eligibility here if needed

        // Call the takeLoan function to process the loan request
        $loanResult = takeLoan($conn, $phoneNumber, (float)$loanAmount);

        if ($loanResult) {
            // Loan request was successful
            $response = "END Your loan request of Ugx $loanAmount has been approved and credited to account $accountNumber. Thank you!\n";
        } else {
            // Loan request failed for some reason
            $response = "END Loan request failed. Please try again later or contact customer support.\n";
        }
    } else {
        // Invalid input for account number or loan amount
        $response = "END Invalid input. Please enter a valid account number and loan amount.\n";
    }
}

elseif ($text == "3") {
    // Handle loan repayment menu
    $loanAmount = fetchLoanAmount($conn, $phoneNumber);
    
    if ($loanAmount > 0) {
        // The user has an outstanding loan, so show loan repayment options
        $response  = "CON Repay Service Debt\n";
        $response .= "Your outstanding service debt amount is UGX $loanAmount.\n";
        $response .= "1. Repay Full Amount\n";
        $response .= "2. Repay Partial Amount\n";
        $response .= "0. Go back to Main Menu\n";
    } else {
        // The user has no outstanding loan
        $response = "CON No outstanding service debt.\n";
        $response .= "0. Go back to Main Menu\n";
    }
} elseif ($text == "3*1") {
   // Fetch the current loan amount
$loanAmount = fetchLoanAmount($conn, $phoneNumber);

if ($loanAmount > 0) {
    // The user has an outstanding loan, so you can proceed with a full loan payment
    $amountToRepay = $loanAmount; // The full loan amount

    // Call the fullLoanPayment function
    $repaymentResult = fullLoanPayment($conn, $phoneNumber, $amountToRepay);

    if ($repaymentResult) {
        // The full loan payment was successful
        $response ="END Full loan repayment successful. Loan has been paid in full.";
    } else {
        // The full loan payment failed for some reason
        $reponsse=  "END Full loan repayment failed. Please try again later or contact customer support.";
    }
} else {
    // The user has no outstanding loan
    echo "No outstanding loan to repay.";
}

} elseif ($text == "3*2") {
   // Check if the user is eligible to make a partial loan repayment
   $loanAmount = fetchLoanAmount($conn, $phoneNumber);

   if ($loanAmount <= 0) {
       $response = "END You have no outstanding loan to repay.\n";
   } else {
       $response = "CON Enter the amount you want to repay:\n";
   }
} else if (is_numeric($text) && $text > 0) {
   // User entered a numeric value for partial loan repayment
   $partialRepaymentAmount = (float)$text;
   
   // Implement partial loan repayment logic
   $repaymentResult = makePartialRepayment($conn, $phoneNumber, $partialRepaymentAmount);
   
   if ($repaymentResult) {
       $response = "END Partial repayment of Ugx $partialRepaymentAmount was successful. Thank you!\n";
   } else {
       $response = "END Partial loan repayment failed. Please try again later.\n";
   }

} elseif ($text == "0") {
    // Allow the user to go back to the main menu from the loan repayment menu
    $response = generateMainMenu();
}
 else {
    // Handle other menu options or invalid input
}


// Close the database connection
$conn->close(); 

// Echo the response back to the Africa's Talking API
header('Content-type: text/plain');
echo $response;


