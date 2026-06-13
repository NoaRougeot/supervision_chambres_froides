<?php
/**
 * Endpoint Server-Sent Events (SSE)
 * Pousse en temps réel les dernières données d'une chambre froide :
 * 
 *  - Dernière température
 *  - Dernier état de porte
 *  - Dernière alerte (acquittée ou non)
 */

require_once __DIR__ . '/../config.php';
require_once BDD_CLASS_PROJET;
require_once PROTECTION_CLASS_PROJET;

session_start();

$protection = new Protection();
$protection->url_protection();
$protection->tfa_url_protection();

$id_chambre = (int)($_GET['id'] ?? 0);

if ($id_chambre <= 0) 
{
    http_response_code(400);
    exit('id_chambre invalide');
}

// En-têtes SSE obligatoires
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no'); // désactive la mise en tampon nginx

//  Connexion BDD
$bdd = new Base_de_donnee();
$bdd->connexion();

if (!$bdd->est_connecter) 
{
    echo "event: erreur\n";
    echo "data: " . json_encode(['message' => 'Impossible de se connecter à la BDD']) . "\n\n";
    flush();
    exit;
}

/**
* Envoie un événement SSE au client.
*/
function sse_envoyer(string $event, array $data): void
{
    echo "event: {$event}\n";
    echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    ob_flush();
    flush();
}

// Boucle de push (toutes les 2 secondes)
$dernier_hash = '';

while (!connection_aborted())
{

    // --- Dernière température ---
    $row_temp = $bdd->fetch(
        "SELECT temperature, horodatage_temperature
         FROM Temperature
         WHERE id_chambre = :id
         ORDER BY horodatage_temperature DESC
         LIMIT 1",
        [':id' => $id_chambre]
    );

    // --- Dernier état de porte ---
    $row_porte = $bdd->fetch(
        "SELECT etat_porte, horodatage_porte
         FROM Porte
         WHERE id_chambre = :id
         ORDER BY horodatage_porte DESC
         LIMIT 1",
        [':id' => $id_chambre]
    );

    // --- Dernière alerte ---
    $row_alerte = $bdd->fetch(
        "SELECT type_alerte, horodatage_alerte, date_ack_alarme
         FROM Alerte
         WHERE id_chambre = :id
         ORDER BY horodatage_alerte DESC
         LIMIT 1",
        [':id' => $id_chambre]
    );

    $payload = [
        'temperature' => $row_temp
            ? [
                'valeur'      => (int)$row_temp['temperature'],
                'horodatage'  => (int)$row_temp['horodatage_temperature'],
              ]
            : null,

        'porte' => $row_porte
            ? [
                'etat'        => (int)$row_porte['etat_porte'],   // 0=fermée, 1=ouverte
                'horodatage'  => (int)$row_porte['horodatage_porte'],
              ]
            : null,

        'alerte' => $row_alerte
            ? [
                'type'        => (int)$row_alerte['type_alerte'],
                'horodatage'  => (int)$row_alerte['horodatage_alerte'],
                'acquittee'   => ((int)$row_alerte['date_ack_alarme'] > 0), // true si timestamp > 0
              ]
            : null,
    ];

    // N'envoie que si les données ont changé (évite le trafic inutile)
    $hash = md5(json_encode($payload));

    if ($hash !== $dernier_hash) 
    {
        sse_envoyer('maj_chambre', $payload);
        $dernier_hash = $hash;
    }

    // Heartbeat toutes les 2 secondes pour maintenir la connexion ouverte
    echo ": heartbeat\n\n";
    ob_flush();
    flush();

    sleep(2);
}
?>