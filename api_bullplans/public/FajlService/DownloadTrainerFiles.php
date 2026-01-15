<?php
include '../hivas.php';  // Adatbázis kapcsolat

$biztonsagiKod = "asd12345678|12345678";
$uploadDir = "SzemelyEdzoLeirasa/";

if (isset($_POST['biztonsagiKod']) && $_POST['biztonsagiKod'] == $biztonsagiKod) {
    if (isset($_POST['email']) && isset($_POST['fileName'])) {
        $email = $_POST['email'];
        $fileName = $_POST['fileName'];

        $filePath = $uploadDir . $email . '/' . $fileName;

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(["error" => "File not found."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Missing email or file name."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["error" => "Invalid security code."]);
}
?>
