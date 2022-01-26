<?php
// Detect the current session
session_start();
// Include the Page Layout header
include("header.php"); 

// Reading inputs entered in previous page
$email = $_POST["email"];
$pwd = $_POST["password"];

//Validate login credentials with database
$checkLogin = false;

// Include the PHP file that establishes database connection handle: $conn
include_once("mysql_conn.php");

// SQL statement to retrieve record in db
$qry = "SELECT ShopperID, Name, Password FROM Shopper WHERE Email=?";

$stmt = $conn->prepare($qry);
// "s" 1 string parameters
$stmt->bind_param("s",$email);
$stmt->execute();
$result1 = $stmt->get_result();
if ($result1->num_rows > 0) {
    $row = $result1->fetch_array();
    //Get the hashed password from database
    $hashed_pwd = $row["Password"];
    //Verifies that a password matches a hash
    if (password_verify($pwd,$hashed_pwd) == true) {
    #if($pwd == $hashed_pwd){ // need to change to hash password later^
        $checkLogin = true;
        // Save user's info in session variables
        $_SESSION["ShopperName"] = $row["Name"];
        $_SESSION["ShopperID"] = $row["ShopperID"];

        // To Do 2 (Practical 4): Get active shopping cart
        $qry = "SELECT sc.ShopCartID, COUNT(sci.ProductID) AS NumItems
        FROM ShopCart sc LEFT JOIN ShopCartItem sci 
        ON sc.ShopCartID=sci.ShopCartID 
        WHERE sc.ShopperID=? AND sc.OrderPlaced=0";
        $stmt = $conn->prepare($qry);
        $stmt->bind_param("i", $_SESSION["ShopperID"]); 
        $stmt->execute();
        $shopCartList = $stmt->get_result();
        $stmt->close();
        if ($shopCartList->num_rows != 0){ // check if theres a active shopping cart
            $row = $shopCartList->fetch_array();
            // Save ShopCartID to session
            $_SESSION["Cart"] = $row["ShopCartID"];
            // Update NumCartItem
            $_SESSION["NumCartItem"] = $row["NumItems"];
        }
    }
    if ($checkLogin == false) { 
        // Password does not match, login unsuccessful
        echo  "<h3 style='color:red'>Invalid Login Credentials</h3>";
        $Message = "Login unsuccessful! Please try again.";
    }
    else{
        // Successful message and ShopperID
        $Message = "Login successful!<br /> Your Shopper ID is $_SESSION[ShopperID]<br/>";
        // Redirect to home page
        header("Location: index.php");
        exit;
    }
    
}       

// Release the resource allocated for prepared statement
$stmt->close();
// close database connection
$conn->close();

// Display Message
echo $Message;
// Include the Page Layout footer
include("footer.php");
?>