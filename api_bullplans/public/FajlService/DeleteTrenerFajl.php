<?php
$biztonsagiKod = "asd12345678|12345678";
$uploadDir = "SzemelyEdzoLeirasa/";

$response = [];

if (isset($_POST['biztonsagiKod']) && $_POST['biztonsagiKod'] == $biztonsagiKod) {
    if (isset($_POST['email']) && isset($_POST['fileName'])) {
        $email = htmlspecialchars($_POST['email']);
        $fileName = htmlspecialchars($_POST['fileName']);

        // Ellenőrizzük, hogy a felhasználó mappája létezik-e
        $userDir = $uploadDir . $email . '/';
        if (is_dir($userDir)) {
            $filePath = $userDir . $fileName;
            
            // Ellenőrizzük, hogy a fájl létezik-e
            if (file_exists($filePath)) {
                if (unlink($filePath)) {
                    $response['success'] = true;
                } else {
                    $response['error'] = "Failed to delete file from server.";
                }
            } else {
                $response['error'] = "File not found.";
            }
        } else {
            $response['error'] = "User directory not found.";
        }
    } else {
        $response['error'] = "Missing email or file name.";
    }
} else {
    $response['error'] = "Invalid security code.";
}

echo json_encode($response);
?>
