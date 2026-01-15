<?php
$biztonsagiKod = "asd12345678|12345678";
$uploadDir = "SzemelyEdzoLeirasa/";

// Állítsd be a karakterkódolást
header('Content-Type: application/json; charset=utf-8');

$response = [];

// Biztonsági kód ellenőrzése
if (isset($_POST['biztonsagiKod']) && $_POST['biztonsagiKod'] === $biztonsagiKod) {
    // Mappák (email címek) lekérése
    $directories = glob($uploadDir . '*', GLOB_ONLYDIR);

    foreach ($directories as $dir) {
        // Email cím kinyerése a mappa nevéből
        $email = basename($dir);

        // Fájlok listázása a mappán belül
        $files = array_diff(scandir($dir), ['.', '..']);

        // Ha vannak fájlok, adjuk hozzá az email-hez társítva
        if (!empty($files)) {
            $response[$email] = array_values($files); // Az értékek a fájlnevek lesznek
        }
    }

    // Ha nincs fájl egy mappában sem
    if (empty($response)) {
        $response['error'] = "No files found.";
    }
} else {
    $response['error'] = "Invalid security code.";
}

// JSON formátumban visszaküldjük a választ
echo json_encode($response);
?>
