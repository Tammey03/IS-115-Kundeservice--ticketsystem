<?php
class Sak
{
    public $title;
    public $category;
    public $priority;
    public $description;
    private $typeErrors = [];
    private $attachment;

    public function __construct($title, $category, $priority, $description, $attachment = null)
    {
        $this->attachment = $attachment;
        $fields = [
            'Tittel' => $title,
            'Kategori' => $category,
            'Prioritet' => $priority,
            'Beskrivelse' => $description

        ];

        foreach ($fields as $label => $value) {
            if (!is_string($value)) {
                $this->typeErrors[] = $label . ' må være tekst.';
            }
        }

        // Bruk trygge tekstverdier også når skjemaet har feil datatype.
        $title = is_string($title) ? $title : '';
        $category = is_string($category) ? $category : '';
        $priority = is_string($priority) ? $priority : '';
        $description = is_string($description) ? $description : '';

        $this->title = trim($title);
        $this->category = $category;
        $this->priority = $priority === "" ? "Normal" : $priority;
        $this->description = trim($description);
    }

    public function valider()
    {
        if (!empty($this->typeErrors)) {
            return array_merge($this->typeErrors, $this->validerVedlegg());
        }

        $errors = [];

        if ($this->title === "") {
            $errors[] = "Du må fylle ut en tittel.";
        }   elseif (mb_strlen($this->title, 'UTF-8') > 150) {
            $errors[] = "Tittelen kan ikke være lengre enn 150 tegn.";
}

        $validCategories = [
            "Innlogging",
            "Betaling",
            "Teknisk problem",
            "Annet"
        ];

        if (!in_array($this->category, $validCategories, true)) {
            $errors[] = "Du må velge en gyldig kategori.";
        }

        if ($this->description === "") {
            $errors[] = "Du må fylle ut en beskrivelse.";
        }

        if (!in_array($this->priority, ["Lav", "Normal", "Høy"], true)) {
            $errors[] = "Ugyldig prioritet.";
        }

        return array_merge($errors, $this->validerVedlegg());
    }

    private function validerVedlegg()
    {
        $file = $this->attachment;

        // Vedlegg er valgfritt.
        if ($file === null) {
            return [];
        }

        if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
            return ['Ugyldig vedlegg.'];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return [];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['Vedlegget kunne ikke lastes opp.'];
        }

        $tmpName = $file['tmp_name'] ?? null;

        if (!is_string($tmpName) || !is_uploaded_file($tmpName)) {
            return ['Ugyldig opplastet fil.'];
        }

        $size = filesize($tmpName);
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if ($size === false || $size === 0) {
            return ['Vedlegget er tomt eller kunne ikke leses.'];
        }

        if ($size > $maxSize) {
            return ['Vedlegget kan ikke være større enn 5 MB.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        if (!in_array($mimeType, $allowedTypes, true)) {
            return ['Vedlegget må være PDF, JPG eller PNG.'];
        }

        return [];
    }
}

?>
