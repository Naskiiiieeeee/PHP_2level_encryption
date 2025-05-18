<?php 
function connection() {
    $server = "localhost";
    $user = "root";
    $pw = "";
    $dbname = "data_encryption";
    $con = new mysqli($server, $user, $pw, $dbname);
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    } else {
        return $con;
    }
}
$con = connection();

if (isset($_POST['btnSubmit'])) {
    $Givendata = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);

    // --------------------------
    // Level 1 AES Encryption
    // --------------------------
    $key1 = "firstSecretKey123"; // 16 characters = 128-bit key
    $iv1 = openssl_random_pseudo_bytes(16); // Generate IV1

    $cipher1 = openssl_encrypt($Givendata, "AES-128-CBC", $key1, 0, $iv1);

    // --------------------------
    // Level 2 AES Encryption
    // --------------------------
    $key2 = "secondSecretKey!@#";
    $iv2 = openssl_random_pseudo_bytes(16); 
    $cipher2 = openssl_encrypt($cipher1, "AES-128-CBC", $key2, 0, $iv2);

    // Encode po muna natin sa base64 for safe DB storage
    $finalEncrypted = base64_encode($cipher2);
    $iv1Encoded = base64_encode($iv1);
    $iv2Encoded = base64_encode($iv2);

    $query = "INSERT INTO test (saveData, iv1, iv2) VALUES (?, ?, ?)";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sss", $finalEncrypted, $iv1Encoded, $iv2Encoded);
        $stmt->execute();
        $stmt->close();
        echo "User data encrypted and saved successfully.";
    } else {
        echo "Error: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <input type="text">
        <button type="submit" name="btnSubmit"> save </button>
    </form>
</body>
</html>
