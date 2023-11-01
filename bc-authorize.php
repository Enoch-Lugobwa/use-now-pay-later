<?php

// Include your database connection script and any necessary functions
include "db_connection.php";
include "functions.php";

$phoneNumber = $_GET['phoneNumber'];
$pin = $_GET['pin'];

// You should have obtained the `consent_id` and `scopes` based on your MTN MOMO API implementation
$consent_id = "your_consent_id";
$scopes = ["scope1", "scope2"];

// Make the `bc-authorize` request (code from previous example)
include "createaccesstoken.php";
// Set the request URL and headers

// Initialize cURL
$curl = curl_init();

// Set cURL options
// ...

// Execute the cURL request
$response = curl_exec($curl);

// Check for errors and get HTTP status code
// ...

// Close the cURL session
curl_close($curl);

// Handle the response (authorize successful or not)
if ($httpcode == 200) {
    // User authorized successfully, continue with database subscription
    subscribeUserToDatabase($conn, $phoneNumber);
    
    // Show the main menu
    header("Location: index.php?phoneNumber=$phoneNumber");
} else {
    // Authorization failed, return an error message
    echo "Authorization failed: $response";
}
?>
