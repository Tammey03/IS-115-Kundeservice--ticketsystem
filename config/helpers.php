<?php

// Gjør tekst trygg å skrive ut i HTML
function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

// Henter og rydder tekst fra et skjema
function formText(string $field): string
{
    $value = $_POST[$field] ?? "";

    return is_string($value) ? trim($value) : "";
}
