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

} elseif (is_numeric($text) && strlen($text) == 5) {
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
}else if ($text == "1") {
    // Check if the user has a valid PIN
    $isValidPin = true;

    // Prompt the user to enter their PIN
    $response = "CON Please enter your 4-digit PIN to check eligibility:\n";
    
    if (is_numeric($text) && strlen($text) == 4) {
        // User entered a 4-digit PIN
        $isValidPin = verifyPin($conn, $phoneNumber, $text);
    }

    if ($isValidPin) {
        // PIN is valid, check eligibility and provide a response
        $eligibility = fetchEligibility($conn, $phoneNumber);
        $response = "CON Your eligibility amount: $eligibility\n";
        $response .= "0. Go back to Main Menu\n";
    }
    // If the PIN is not valid, it will still be in the "CON" state, and the user can retry.
}else if ($text == "4") {
    // User selected "Unsubscribe"
    // Call the unsubscribe function to change subscription status to 0
    unsubscribeUser($conn, $phoneNumber);
    $response = "END You have been unsubscribed. Thank you for using our service.\n";
}else if ($text == "2") { // User selected "Request Loan"
    // Show Service request categories
    $response  = "CON Select Service Category\n";
    $response .= "1. Utilities\n";
    $response .= "2. Pay TV\n";
    $response .= "3. Gas\n";
} else if ($text == "2*1") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Service\n";
    $response .= "1. Umeme\n";
    $response .= "2. NWSC\n";
    $response .= "3. Solar\n";
}else if ($text == "2*1*1") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Service\n";
    $response .= "1. Yaka\n";
    $response .= "2. Bill\n";
}else if ($text == "2*1*1*1") { // User selected "Utilities" and "Umeme"
   $response  = "CON Enter Yaka Meter Number:\n";
  
}else if ($text == "2*1*1*2") { // User selected "Utilities" and "Umeme"
   $response  = "CON Enter Acc No:\n";
  
}else if ($text == "2*1*2") { // User selected "Utilities" and "Umeme"
   $response  = "CON Enter Acc No:\n";
  
}else if ($text == "2*1*2") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Service ";
   $response .= "1. Umeme\n";
   $response .= "2. NWSC\n";
   $response .= "3. Solar\n";
  
}else if ($text == "2*1*3") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Service\n ";
   $response .= "1. Solar Now\n";
   $response .= "2. M-Kopa Solar\n";
  
}else if ($text == "2*2") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Service\n ";
   $response .= "1. DSTV\n";
   $response .= "2. GOTV\n";
   $response .= "3. Azam TV\n";
   $response .= "4. Star Times\n";
   $reponsse .= "5. YOTV\n";
  
}else if ($text == "2*2*1") { // User selected "Utilities" and "Umeme"
   $response  = "CON Select Package\n ";
   $response .= "1. Lumba 16000/-\n";
   $response .= "2. Access 43000/-\n";
   $response .= "3. Family 64000/-\n";
   $response .= "4. Compact 104000/-\n";
   $response .= "5. Compact Plus 160000/-\n";
  
}else if ($text == "2*2*2") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Package\n ";
    $response .= "1. Lite 15000/- \n";
    $response .= "2. Value 21000/-\n";
    $response .= "3. Plus 33000/-\n";
    $response .= "4. Max 49000/-\n";
    $response .= "5. Supa 64000/-\n";
    $response .= "6. Supa+ 104000/-\n";
    
   
 }else if ($text == "2*2*4") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Services\n ";
    $response .= "1. Daily \n";
    $response .= "2. Weekly\n";
    $response .= "3. Monthly\n";    
   
 }else if ($text == "2*2*4*1") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Package\n ";
    $response .= "1. Nova 1000/-\n";
    $response .= "2. Basic 2000/-\n";
    $response .= "3. Classic 3000/-\n";    

 }else if ($text == "2*2*4*2") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Package\n ";
    $response .= "1. Nova 3500/-\n";
    $response .= "2. Basic 6600/-\n";
    $response .= "3. Classic 12000/-\n"; 

 }else if ($text == "2*2*4*2") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Package\n ";
    $response .= "1. Nova 11000/-\n";
    $response .= "2. Basic 20000/-\n";
    $response .= "3. Classic 26000/-\n";    
 }
 else if ($text == "2*2*3") { // User selected "Utilities" and "Umeme"
    $response  = "CON Select Package\n ";
    $response .= "1. Pure(1 Week) 5000/-\n";
    $response .= "2. Pure 130000/-\n";
    $response .= "3. Plus 30000/-\n";
    $response .= "4. Play 45000/-\n";    
 }else if($text == "2*2*5"){
    $response = "CON Select Package\n";
    $response .="1. 1 Hr 800/-\n";
    $reponsse .="2. 1 Day 1,500/-\n";
    $reponsse .="3. 1 Week 7500/-\n";
    $response .="4. 1 Month 20000/-\n";

} else if ($text == "3") {
    // Handle loan repayment logic
    $loanAmount = fetchLoanAmount($conn, $phoneNumber);
    
    if ($loanAmount > 0) {
        // The user has an outstanding loan to repay
        $response  = "CON Repay Service Debt\n";
        $response .= "Your outstanding service debt amount is UGX $loanAmount.\n";
        $response .= "1. Repay Full Amount\n";
        $response .= "2. Repay Partial Amount\n";
        $response .= "0. Go back to Main Menu\n";
    } else {
        // The user does not have an outstanding loan
        $response = "CON No outstanding service debt.\n";
        $response .= "0. Go back to Main Menu\n";
    }
} else if ($text == "3*1") {
    // User selected to repay the full loan amount
    $loanAmount = fetchLoanAmount($conn, $phoneNumber);
    
    if ($loanAmount > 0) {
        // Deduct the full loan amount from the user's account
        // Implement the deduction logic based on your use case
        $deductionSuccessful = deductFullLoanAmount($conn, $phoneNumber, $loanAmount);
        
        if ($deductionSuccessful) {
            $response = "END Service repayment successful. Thank you!\n";
        } else {
            $response = "END Service repayment failed. Please try again later.\n";
        }
    } else {
        // The user does not have an outstanding loan
        $response = "END You do not have an outstanding Service debt.\n";
    }
} else if ($text == "3*2") {
    // User selected to repay a partial loan amount
    $response = "CON Enter the amount you want to repay:\n";
} else if (is_numeric($text) && $text > 0) {
    // User entered a numeric value for partial loan repayment
    $partialRepaymentAmount = (float)$text;
    
    // Implement partial loan repayment logic based on your use case
    $partialRepaymentSuccessful = makePartialRepayment($conn, $phoneNumber, $partialRepaymentAmount);
    
    if ($partialRepaymentSuccessful) {
        $response = "END Partial  repayment of Ugx $partialRepaymentAmount was successful. Thank you!\n";
    } else {
        $response = "END Partial Service repayment failed. Please try again later.\n";
    }
}
// Close the database connection
$conn->close(); 

// Echo the response back to the Africa's Talking API
header('Content-type: text/plain');
echo $response;


