<?php

/**
 * FormHandler - Eine Klasse zur Validierung und Verarbeitung von Formulardaten
 * Diese Klasse bietet Methoden zur Validierung von Eingabefeldern,
 * Fehlerbehandlung und sicheren Verarbeitung von Formulardaten.
 */
class formhandler {
    private $data = [];
    private $errors = [];
    private $sanitizedData = [];
    
    /**
     * Konstruktor, der die Formulardaten übernimmt
     * 
     * @param array $formData Die zu verarbeitenden Formulardaten
     */
    public function __construct(array $formData = []) {
        $this->data = $formData;
    }
    
    /**
     * Setzt Formulardaten oder fügt neue hinzu
     * 
     * @param array $formData Die zu verarbeitenden Formulardaten
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function setData(array $formData): FormHandler {
        $this->data = $formData;
        return $this;
    }
    
    /**
     * Prüft, ob ein Feld existiert und nicht leer ist
     * 
     * @param string $field Der Feldname
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function required(string $field, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Feld '$field' ist erforderlich.";
        
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob ein Feld eine gültige E-Mail-Adresse enthält
     * 
     * @param string $field Der Feldname
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function email(string $field, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Feld '$field' muss eine gültige E-Mail-Adresse enthalten.";
        
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob ein Feld eine Mindestlänge hat
     * 
     * @param string $field Der Feldname
     * @param int $length Die Mindestlänge
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function minLength(string $field, int $length, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Feld '$field' muss mindestens $length Zeichen lang sein.";
        
        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob ein Feld eine maximale Länge nicht überschreitet
     * 
     * @param string $field Der Feldname
     * @param int $length Die maximale Länge
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function maxLength(string $field, int $length, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Feld '$field' darf maximal $length Zeichen lang sein.";
        
        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob ein Feld einen numerischen Wert enthält
     * 
     * @param string $field Der Feldname
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function numeric(string $field, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Feld '$field' muss einen numerischen Wert enthalten.";
        
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob ein Passwort den Sicherheitsanforderungen entspricht
     * 
     * @param string $field Der Feldname
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function strongPassword(string $field, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Das Passwort muss mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten.";
        
        if (isset($this->data[$field])) {
            $password = $this->data[$field];
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);
            
            if (!$uppercase || !$lowercase || !$number || !$specialChars) {
                $this->errors[$field] = $errorMessage;
            }
        }
        
        return $this;
    }
    
    /**
     * Prüft, ob zwei Felder übereinstimmen (z.B. für Passwort-Bestätigung)
     * 
     * @param string $field1 Das erste Feldname
     * @param string $field2 Das zweite Feldname
     * @param string $errorMessage Optionale Fehlermeldung
     * @return FormHandler Die aktuelle Instanz für Method Chaining
     */
    public function match(string $field1, string $field2, string $errorMessage = null): FormHandler {
        $errorMessage = $errorMessage ?? "Die Felder '$field1' und '$field2' müssen übereinstimmen.";
        
        if (isset($this->data[$field1]) && isset($this->data[$field2]) && $this->data[$field1] !== $this->data[$field2]) {
            $this->errors[$field2] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Sanitiert alle vorhandenen Daten und gibt sie zurück
     * 
     * @return array Die sanitierten Daten
     */
    public function sanitize(): array {
        $this->sanitizedData = [];
        
        foreach ($this->data as $key => $value) {
            // Grundlegende Sanitierung für alle Felder
            $this->sanitizedData[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        
        return $this->sanitizedData;
    }
    
    /**
     * Prüft, ob die Validierung erfolgreich war
     * 
     * @return bool True, wenn keine Fehler gefunden wurden
     */
    public function isValid(): bool {
        return empty($this->errors);
    }
    
    /**
     * Gibt alle Fehler zurück
     * 
     * @return array Die Fehlermeldungen
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Gibt alle Fehler als HTML-Liste zurück
     * 
     * @return string Die Fehlermeldungen als HTML-Liste
     */
    public function getErrorsHtml(): string {
        if (empty($this->errors)) {
            return '';
        }
        
        $html = '<ul class="form-errors">';
        foreach ($this->errors as $field => $error) {
            $html .= '<li data-field="' . $field . '">' . $error . '</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Gibt den Fehlerstatus eines bestimmten Feldes zurück
     * 
     * @param string $field Der Feldname
     * @return string|null Die Fehlermeldung oder null
     */
    public function getFieldError(string $field): ?string {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Gibt die sanitierten Daten zurück
     * 
     * @return array Die sanitierten Daten
     */
    public function getSanitizedData(): array {
        if (empty($this->sanitizedData)) {
            $this->sanitize();
        }
        
        return $this->sanitizedData;
    }
}


/**
<?php
// Formular wurde gesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formHandler = new FormHandler($_POST);
    
    // Validierungsregeln definieren
    $formHandler->required('name')
                ->required('email')
                ->email('email')
                ->required('password')
                ->minLength('password', 8)
                ->strongPassword('password')
                ->required('password_confirm')
                ->match('password', 'password_confirm', 'Die Passwörter stimmen nicht überein.');
    
    // Prüfen, ob das Formular gültig ist
    if ($formHandler->isValid()) {
        // Daten sanitieren und verarbeiten
        $sanitizedData = $formHandler->sanitize();
        
        // Hier könnte die weitere Verarbeitung erfolgen, z.B. Speichern in einer Datenbank
        // ...
        
        // Weiterleitung oder Erfolgsmeldung
        echo "Formular erfolgreich gesendet!";
    } else {
        // Fehler anzeigen
        echo $formHandler->getErrorsHtml();
    }
}



*/