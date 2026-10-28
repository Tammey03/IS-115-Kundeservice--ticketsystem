<?php
class Sak
{
    public $title;
    public $category;
    public $priority;
    public $description;
    private $typeErrors = [];

    public function __construct($title, $category, $priority, $description)
    {
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
            return $this->typeErrors;
        }

        $errors = [];

        if ($this->title === "") {
            $errors[] = "Du må fylle ut en tittel.";
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

        return $errors;
    }
}

?>
