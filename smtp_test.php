<?php
// Lade PHPMailer-Klassen
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Lade Composer Autoloader (wenn du PHPMailer via Composer installiert hast)
require 'vendor/autoload.php';
// --- Alternativ: Manueller Include, falls du PHPMailer manuell heruntergeladen hast ---
// Kopiere den 'src' Ordner von PHPMailer in dein Projektverzeichnis, z.B. als 'PHPMailer'
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';
// -------------------------------------------------------------------------------------

$message = '';
$error_message = '';
$smtp_debug_output = '';

// Standardwerte für das Formular
$defaults = [
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => '587',
    'smtp_secure' => 'tls', // 'ssl' oder 'tls' oder 'none'
    'smtp_user' => '', // Standardmäßig leer lassen
    'smtp_pass' => '', // Standardmäßig leer lassen
    'from_email' => 'absender@example.com',
    'from_name' => 'SMTP Tester',
    'to_email' => 'empfaenger@example.com',
    'subject' => 'Test E-Mail via PHP SMTP',
    'body' => "Hallo,\n\ndies ist eine Test-E-Mail, gesendet über PHPMailer mit SMTP.\n\nViele Grüße,\nDein PHP-Skript",
];

// Formularwerte mit Standardwerten zusammenführen (für das erneute Anzeigen im Formular)
$form_values = $_POST + $defaults; // $_POST überschreibt $defaults, wenn vorhanden

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Instanziiere PHPMailer
    $mail = new PHPMailer(true); // true aktiviert Exceptions

    try {
        // Server Einstellungen
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Aktiviere ausführliche Debug-Ausgabe
                                               // SMTP::DEBUG_OFF für keine Ausgabe
                                               // SMTP::DEBUG_CLIENT für Client-Meldungen
                                               // SMTP::DEBUG_SERVER für Client- und Server-Meldungen
                                               // SMTP::DEBUG_CONNECTION für Verbindungsdetails
                                               // SMTP::DEBUG_LOWLEVEL für detaillierte Low-Level-Infos

        // Fange die Debug-Ausgabe ab, damit sie nicht das HTML stört
        ob_start();

        $mail->isSMTP();
        $mail->Host       = $_POST['smtp_host'];

        // SMTP Authentifizierung nur aktivieren, wenn ein Benutzername angegeben wurde
        if (!empty($_POST['smtp_user'])) {
            $mail->SMTPAuth   = true;
            $mail->Username   = $_POST['smtp_user'];
            $mail->Password   = $_POST['smtp_pass']; // Passwort kann leer sein, wenn der Server das erlaubt
        } else {
            $mail->SMTPAuth   = false; // Keine SMTP-Authentifizierung
        }

        if ($_POST['smtp_secure'] !== 'none') {
            $mail->SMTPSecure = $_POST['smtp_secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = false; // Keine Verschlüsselung
            $mail->SMTPAutoTLS = false; // Deaktiviere AutoTLS, wenn 'none' explizit gewählt
        }

        $mail->Port       = (int)$_POST['smtp_port'];
        $mail->CharSet    = 'UTF-8'; // Wichtig für Sonderzeichen

        // Empfänger
        $mail->setFrom($_POST['from_email'], $_POST['from_name']);
        $mail->addAddress($_POST['to_email']); // Name ist optional: $mail->addAddress('joe@example.net', 'Joe User');

        // Inhalt
        $mail->isHTML(false); // Sende als Plain-Text E-Mail. Setze auf true für HTML.
        $mail->Subject = $_POST['subject'];
        $mail->Body    = $_POST['body'];
        // $mail->AltBody = 'Dies ist der Body in Plain-Text für nicht-HTML Mail Clients'; // Falls isHTML(true)

        $mail->send();
        $message = 'E-Mail wurde erfolgreich gesendet!';

    } catch (Exception $e) {
        $error_message = "E-Mail konnte nicht gesendet werden. PHPMailer Fehlermeldung: {$mail->ErrorInfo}";
        // Du kannst auch $e->getMessage() verwenden, aber $mail->ErrorInfo ist oft spezifischer für SMTP-Probleme.
    } finally {
        // Hole die Debug-Ausgabe, auch wenn ein Fehler auftritt
        $smtp_debug_output = ob_get_clean();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>PHP SMTP E-Mail Tester</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover { background-color: #45a049; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .debug-output {
            background-color: #333;
            color: #fff;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap; /* Wichtig für Zeilenumbrüche */
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }
        .debug-output h3 { margin-top: 0; color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP SMTP E-Mail Tester</h1>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>SMTP Server Einstellungen</h2>
            <label for="smtp_host">SMTP Host:</label>
            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($form_values['smtp_host']); ?>" required>

            <label for="smtp_port">SMTP Port:</label>
            <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($form_values['smtp_port']); ?>" required>
            <small>Üblich: 587 (TLS), 465 (SSL), 25 (unverschlüsselt, nicht empfohlen)</small><br><br>

            <label for="smtp_secure">Verschlüsselung:</label>
            <select id="smtp_secure" name="smtp_secure">
                <option value="tls" <?php echo ($form_values['smtp_secure'] == 'tls') ? 'selected' : ''; ?>>TLS (STARTTLS)</option>
                <option value="ssl" <?php echo ($form_values['smtp_secure'] == 'ssl') ? 'selected' : ''; ?>>SSL</option>
                <option value="none" <?php echo ($form_values['smtp_secure'] == 'none') ? 'selected' : ''; ?>>Keine</option>
            </select><br><br>

            <label for="smtp_user">SMTP Benutzername (optional):</label>
            <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($form_values['smtp_user']); ?>">

            <label for="smtp_pass">SMTP Passwort (optional, nur wenn Benutzername angegeben):</label>
            <input type="password" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($form_values['smtp_pass']); ?>">

            <h2>E-Mail Details</h2>
            <label for="from_email">Von E-Mail:</label>
            <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars($form_values['from_email']); ?>" required>

            <label for="from_name">Von Name:</label>
            <input type="text" id="from_name" name="from_name" value="<?php echo htmlspecialchars($form_values['from_name']); ?>">

            <label for="to_email">An E-Mail:</label>
            <input type="email" id="to_email" name="to_email" value="<?php echo htmlspecialchars($form_values['to_email']); ?>" required>

            <label for="subject">Betreff:</label>
            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($form_values['subject']); ?>" required>

            <label for="body">Nachricht (Plain Text):</label>
            <textarea id="body" name="body" rows="8" required><?php echo htmlspecialchars($form_values['body']); ?></textarea>

            <input type="submit" value="E-Mail senden">
        </form>

        <?php if ($smtp_debug_output): ?>
            <div class="debug-output">
                <h3>SMTP Debug-Ausgabe:</h3>
                <?php echo nl2br(htmlspecialchars($smtp_debug_output)); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
