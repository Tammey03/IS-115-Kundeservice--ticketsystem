<?php
/*
Klassen klargjør og validerer saksdata.
Den lagrer verken saker eller vedlegg. Lagring av selve saken
håndteres i opprettSak.php.
*/

class Sak
{
    public $title;
    public $category;
    public $priority;
    public $description;
    private $typeErrors = [];
    private $attachment;

    /*Klargjør saksdata for validering ved å kontrollere datatyper,
    fjerne mellomrom rundt tittel og beskrivelse og bruke
     Normal som prioritet når prioriteten er tom. */
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

        /* Bruker tom tekst ved feil datatype slik at trim() og andre
            tekstfunksjoner ikke får verdier de ikke kan behandle. */
        $title = is_string($title) ? $title : '';
        $category = is_string($category) ? $category : '';
        $priority = is_string($priority) ? $priority : '';
        $description = is_string($description) ? $description : '';


$this->title = trim($title);
$this->category = trim($category);
$this->priority = trim($priority) === '' ? 'Normal' : trim($priority);
$this->description = trim($description);

    }
    //Denne funksjonen validerer saksdataene og returnerer en liste med eventuelle feil. 
    //Hvis det er ingen feil, returnerer den listen som tom.
    public function valider()
    {
        if (!empty($this->typeErrors)) {
            return array_merge($this->typeErrors, self::validerVedlegg($this->attachment));
        }

        $errors = [];
        /* Bruker mb_strlen() slik at norske bokstaver som æ, ø og å 
            telles som ett tegn hver i UTF-8. */
        if ($this->title === "") {
            $errors[] = "Du må fylle ut en tittel.";
        }   elseif (mb_strlen($this->title, 'UTF-8') > 150) {
            $errors[] = "Tittelen kan ikke være lengre enn 150 tegn.";
}
        /* Kontrollerer tillatte valg på serveren fordi verdiene
         fra skjemaet kan manipuleres utenfor nettleserens meny.*/
        $validCategories = [
            "Vann og avløp",
            "Strøm og varme",
            "Internett",
            "Skader og vedlikehold",
            "Nøkler og adgang",
            "Renhold og fellesområder",
            "Støy og naboforhold",
            "Husleie og kontrakt",
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

        return array_merge($errors, self::validerVedlegg($this->attachment));
    }
    //Denne funksjonen validerer vedlegget og returnerer en liste med eventuelle feil.
    public static function validerVedlegg($file): array
    {
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

        if (!is_string($file['name'] ?? null)) {
            return ['Ugyldig filnavn.'];
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

        /*Undersøker filinnholdets MIME-type fordi filendelsen alene
        ikke er tilstrekkelig til å avgjøre filtypen.*/

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
