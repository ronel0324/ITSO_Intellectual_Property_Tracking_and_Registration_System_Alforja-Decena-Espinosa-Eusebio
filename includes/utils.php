<?php
function calculateExpiration($classification, $date_submitted_to_ipophil) {
    if (empty($date_submitted_to_ipophil)) {
        return null;
    }

    $years = [
        "Copyright" => 50,
        "Patent" => 20,
        "Trademark" => 10,
        "Utility Model" => 7,
        "Industrial Design" => 5
    ];

    $normalized = ucwords(strtolower(trim($classification)));

    return isset($years[$normalized])
        ? date('Y-m-d', strtotime("+{$years[$normalized]} years", strtotime($date_submitted_to_ipophil)))
        : null;
}
