<?php
/**
 * Script: FixOrphanCertificates.php
 *
 * Risolve i certificati "orfani" - certificati generati prima dell'iscrizione
 * corrente ma senza record nella history.
 *
 * Uso:
 *   php FixOrphanCertificates.php [--dry-run] [--database=nome_db]
 *
 * Opzioni:
 *   --dry-run     Mostra cosa verrebbe fatto senza eseguire modifiche
 *   --database    Nome del database (default: usa la configurazione di FormaLMS)
 *
 * IMPORTANTE: Eseguire SEMPRE un backup del database prima!
 *
 * @author Claude Code
 * @date 2026-01-16
 */

// Configurazione - usa le stesse variabili d'ambiente del config.php di FormaLMS
$config = [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_port' => 3306,
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: 'root',
    'db_name' => getenv('DB_NAME') ?: 'forma', // Default, può essere sovrascritto da --database
];

// Parse argomenti
$dryRun = in_array('--dry-run', $argv);
foreach ($argv as $arg) {
    if (strpos($arg, '--database=') === 0) {
        $config['db_name'] = substr($arg, 11);
    }
}

echo "=============================================================\n";
echo "FixOrphanCertificates - Riparazione certificati orfani\n";
echo "=============================================================\n";
echo "Database: {$config['db_name']}\n";
echo "Modalità: " . ($dryRun ? "DRY-RUN (nessuna modifica)" : "ESECUZIONE") . "\n";
echo "=============================================================\n\n";

// Connessione al database
try {
    $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[OK] Connessione al database riuscita\n\n";
} catch (PDOException $e) {
    die("[ERRORE] Connessione fallita: " . $e->getMessage() . "\n");
}

// STEP 1: Analisi - Trovare certificati orfani
echo "STEP 1: Analisi certificati orfani...\n";
echo str_repeat("-", 60) . "\n";

$queryOrphans = "
    SELECT
        ca.id as cert_id,
        ca.id_user,
        ca.id_course,
        ca.id_certificate,
        ca.on_date as cert_date,
        ca.idHistory,
        u.userid,
        c.name as course_name,
        cu.date_inscr as current_inscr,
        cu.status
    FROM learning_certificate_assign ca
    JOIN core_user u ON u.idst = ca.id_user
    JOIN learning_course c ON c.idCourse = ca.id_course
    JOIN learning_courseuser cu ON cu.idUser = ca.id_user AND cu.idCourse = ca.id_course
    LEFT JOIN learning_courseuser_history h ON h.idUser = ca.id_user AND h.idCourse = ca.id_course
    WHERE ca.on_date < cu.date_inscr
      AND (ca.idHistory IS NULL OR ca.idHistory = 0)
      AND h.idHistory IS NULL
";

$orphans = $pdo->query($queryOrphans)->fetchAll();

if (empty($orphans)) {
    echo "[OK] Nessun certificato orfano trovato!\n";
    exit(0);
}

echo "Trovati " . count($orphans) . " certificati orfani:\n\n";

foreach ($orphans as $orphan) {
    echo "  - Cert ID: {$orphan['cert_id']}\n";
    echo "    Utente: {$orphan['userid']} (ID: {$orphan['id_user']})\n";
    echo "    Corso: {$orphan['course_name']} (ID: {$orphan['id_course']})\n";
    echo "    Data certificato: {$orphan['cert_date']}\n";
    echo "    Iscrizione corrente: {$orphan['current_inscr']}\n";
    echo "\n";
}

if ($dryRun) {
    echo "[DRY-RUN] Nessuna modifica eseguita.\n";
    echo "Rimuovere --dry-run per eseguire le correzioni.\n";
    exit(0);
}

// Conferma prima di procedere
echo "Procedere con la correzione? (s/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim(strtolower($line)) !== 's') {
    echo "Operazione annullata.\n";
    exit(0);
}

// STEP 2: Creare record history mancanti
echo "\nSTEP 2: Creazione record history mancanti...\n";
echo str_repeat("-", 60) . "\n";

$pdo->beginTransaction();

try {
    foreach ($orphans as $orphan) {
        // Calcola date stimate
        $certDate = new DateTime($orphan['cert_date']);
        $dateInscr = (clone $certDate)->modify('-30 days')->format('Y-m-d H:i:s');
        $dateComplete = (clone $certDate)->modify('-1 day')->format('Y-m-d H:i:s');

        // Inserisci record history
        $insertHistory = $pdo->prepare("
            INSERT INTO learning_courseuser_history (
                idUser, idCourse, edition_id, level, date_inscr, date_first_access,
                date_complete, status, waiting, subscribed_by, rule_log, score_given,
                imported_from_connection, absent, cancelled_by, new_forum_post,
                date_begin_validity, date_expire_validity, requesting_unsubscribe,
                requesting_unsubscribe_date, learning_course_report_sent
            ) VALUES (
                :idUser, :idCourse, 0, 3, :dateInscr, :dateInscr,
                :dateComplete, 2, 0, 0, NULL, NULL,
                NULL, 0, NULL, 0,
                NULL, NULL, 0,
                NULL, 0
            )
        ");

        $insertHistory->execute([
            ':idUser' => $orphan['id_user'],
            ':idCourse' => $orphan['id_course'],
            ':dateInscr' => $dateInscr,
            ':dateComplete' => $dateComplete,
        ]);

        $historyId = $pdo->lastInsertId();

        // Aggiorna idHistory sul certificato
        $updateCert = $pdo->prepare("
            UPDATE learning_certificate_assign
            SET idHistory = :historyId
            WHERE id = :certId
        ");

        $updateCert->execute([
            ':historyId' => $historyId,
            ':certId' => $orphan['cert_id'],
        ]);

        echo "  [OK] Cert ID {$orphan['cert_id']}: creato history ID {$historyId}\n";
    }

    $pdo->commit();
    echo "\n[OK] Tutte le correzioni applicate con successo!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n[ERRORE] Rollback eseguito: " . $e->getMessage() . "\n";
    exit(1);
}

// STEP 3: Verifica finale
echo "\nSTEP 3: Verifica finale...\n";
echo str_repeat("-", 60) . "\n";

$remaining = $pdo->query($queryOrphans)->fetchAll();

if (empty($remaining)) {
    echo "[OK] Nessun certificato orfano rimanente!\n";
} else {
    echo "[ATTENZIONE] Rimangono " . count($remaining) . " certificati orfani.\n";
}

// Statistiche finali
$stats = $pdo->query("
    SELECT
        COUNT(*) as totale,
        SUM(CASE WHEN idHistory IS NULL OR idHistory = 0 THEN 1 ELSE 0 END) as senza_history,
        SUM(CASE WHEN idHistory > 0 THEN 1 ELSE 0 END) as con_history
    FROM learning_certificate_assign
")->fetch();

echo "\nStatistiche finali:\n";
echo "  - Totale certificati: {$stats['totale']}\n";
echo "  - Con idHistory: {$stats['con_history']}\n";
echo "  - Senza idHistory: {$stats['senza_history']}\n";

echo "\n[COMPLETATO]\n";
