<?php
include '../hivas.php'; // Adatbázis kapcsolat

$biztonsagiKod = "asd12345678|12345678";
$allowedExtensions = [
    'png', 'jpeg', 'jpg', 'docx', 'pdf', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', 
    'gif', 'bmp', 'rtf'
];

// Engedélyezett MIME típusok
$allowedMimeTypes = [
    "image/jpeg", "image/png", "application/msword", 
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "text/plain", "application/pdf", "application/vnd.ms-excel", 
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "application/vnd.ms-powerpoint", 
    "application/vnd.openxmlformats-officedocument.presentationml.presentation", 
    "image/gif", "image/bmp", "text/rtf"
];

$uploadDir = "SzemelyEdzoLeirasa/";

// Állítsd be a karakterkódolást
header('Content-Type: application/json; charset=utf-8');
mysqli_set_charset($db, "utf8mb4");

$response = [];

if (isset($_POST['biztonsagiKod']) && $_POST['biztonsagiKod'] == $biztonsagiKod) {
    if (isset($_POST['email']) && isset($_POST['fileName']) && isset($_FILES['file'])) {
        $email = $db->real_escape_string($_POST['email']);
        $fileName = $db->real_escape_string($_POST['fileName']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileMimeType = mime_content_type($_FILES['file']['tmp_name']);

        if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileMimeType, $allowedMimeTypes)) {
            $response['error'] = "Invalid file type. Please upload a valid file.";
        } else {
            // Célmappa létrehozása (felhasználó emailje alapján)
            $userDir = $uploadDir . $email . '/';
            if (!is_dir($userDir)) {
                mkdir($userDir, 0777, true);
            }

            $targetFile = $userDir . basename($fileName);

            // Ellenőrizzük, hogy a fájl feltöltődött-e
            if (is_uploaded_file($_FILES['file']['tmp_name'])) {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                    $response['success'] = true;
                } else {
                    $response['error'] = "Error occurred while uploading the file.";
                }
            } else {
                $response['error'] = "Failed to upload file.";
            }
        }
    } else {
        $response['error'] = "Missing email or file name.";
    }
} else {
    $response['error'] = "Invalid security code.";
}

echo json_encode($response);

$db->close();
?>
