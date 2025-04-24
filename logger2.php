<?php

/**
 * Logger - Eine flexible Klasse zur Protokollierung von Ereignissen und Fehlern
 * 
 * Diese Klasse bietet verschiedene Methoden zur Protokollierung von Nachrichten
 * in Dateien, Datenbanken oder per E-Mail mit unterschiedlichen Schweregrad-Stufen.
 */
class Logger {
    // Log-Level Konstanten
    const LEVEL_DEBUG = 100;
    const LEVEL_INFO = 200;
    const LEVEL_NOTICE = 300;
    const LEVEL_WARNING = 400;
    const LEVEL_ERROR = 500;
    const LEVEL_CRITICAL = 600;
    const LEVEL_ALERT = 700;
    const LEVEL_EMERGENCY = 800;
    
    // Mapping von Log-Level zu lesbareren Namen
    private $levelNames = [
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_NOTICE => 'NOTICE',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
        self::LEVEL_CRITICAL => 'CRITICAL',
        self::LEVEL_ALERT => 'ALERT',
        self::LEVEL_EMERGENCY => 'EMERGENCY',
    ];
    
    // Minimales Log-Level, das protokolliert werden soll
    private $minLevel;
    
    // Log-Handler (Datei, Datenbank, etc.)
    private $handlers = [];
    
    // Zusätzliche Kontextinformationen
    private $context = [];
    
    /**
     * Konstruktor
     * 
     * @param int $minLevel Minimales Log-Level (Standard: DEBUG)
     */
    public function __construct(int $minLevel = self::LEVEL_DEBUG) {
        $this->minLevel = $minLevel;
        
        // Standard-Kontext hinzufügen
        $this->addContext('timestamp', date('Y-m-d H:i:s'));
        $this->addContext('ip', $_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $this->addContext('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
    }
    
    /**
     * Fügt einen Handler für Log-Nachrichten hinzu
     * 
     * @param callable $handler Eine Funktion, die die Log-Nachricht verarbeitet
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function addHandler(callable $handler): Logger {
        $this->handlers[] = $handler;
        return $this;
    }
    
    /**
     * Fügt einen Datei-Handler hinzu
     * 
     * @param string $filePath Pfad zur Log-Datei
     * @param bool $appendDate Ob das aktuelle Datum an den Dateinamen angehängt werden soll
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function addFileHandler(string $filePath, bool $appendDate = true): Logger {
        if ($appendDate) {
            $pathInfo = pathinfo($filePath);
            $filePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . date('Y-m-d') . '.' . ($pathInfo['extension'] ?? 'log');
        }
        
        $handler = function(int $level, string $message, array $context) use ($filePath) {
            $logEntry = $this->formatLogEntry($level, $message, $context);
            file_put_contents($filePath, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        };
        
        $this->addHandler($handler);
        return $this;
    }
    
    /**
     * Fügt einen E-Mail-Handler hinzu
     * 
     * @param string $toEmail E-Mail-Adresse des Empfängers
     * @param int $minLevel Minimales Level, ab dem eine E-Mail gesendet wird
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function addEmailHandler(string $toEmail, int $minLevel = self::LEVEL_ERROR): Logger {
        $handler = function(int $level, string $message, array $context) use ($toEmail, $minLevel) {
            if ($level < $minLevel) {
                return;
            }
            
            $subject = 'Log Alert: ' . $this->levelNames[$level] . ' - ' . substr($message, 0, 50);
            $body = $this->formatLogEntry($level, $message, $context);
            
            mail($toEmail, $subject, $body);
        };
        
        $this->addHandler($handler);
        return $this;
    }
    
    /**
     * Fügt einen Kontextparameter hinzu
     * 
     * @param string $key Schlüssel für den Kontext
     * @param mixed $value Wert für den Kontext
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function addContext(string $key, $value): Logger {
        $this->context[$key] = $value;
        return $this;
    }
    
    /**
     * Formatiert einen Log-Eintrag
     * 
     * @param int $level Log-Level
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return string Der formatierte Log-Eintrag
     */
    private function formatLogEntry(int $level, string $message, array $context): string {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = $this->levelNames[$level] ?? 'UNKNOWN';
        
        $entry = "[$timestamp] [$levelName] $message";
        
        if (!empty($context)) {
            $contextStr = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $entry .= " | Context: $contextStr";
        }
        
        return $entry;
    }
    
    /**
     * Protokolliert eine Nachricht
     * 
     * @param int $level Log-Level
     * @param string $message Die Nachricht
     * @param array $additionalContext Zusätzlicher Kontext für diesen Log-Eintrag
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function log(int $level, string $message, array $additionalContext = []): bool {
        if ($level < $this->minLevel) {
            return false;
        }
        
        // Kontext zusammenführen
        $context = array_merge($this->context, $additionalContext);
        
        // Backtrace für Fehler hinzufügen
        if ($level >= self::LEVEL_ERROR) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $caller = $backtrace[1] ?? [];
            
            $context['file'] = $caller['file'] ?? 'unknown';
            $context['line'] = $caller['line'] ?? 'unknown';
            $context['function'] = $caller['function'] ?? 'unknown';
        }
        
        // Handler aufrufen
        foreach ($this->handlers as $handler) {
            call_user_func($handler, $level, $message, $context);
        }
        
        return true;
    }
    
    /**
     * Protokolliert eine Debug-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function debug(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Protokolliert eine Info-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function info(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Protokolliert eine Hinweis-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function notice(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_NOTICE, $message, $context);
    }
    
    /**
     * Protokolliert eine Warnungs-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function warning(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Protokolliert eine Fehler-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function error(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Protokolliert eine kritische Fehler-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function critical(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * Protokolliert eine Alarm-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function alert(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_ALERT, $message, $context);
    }
    
    /**
     * Protokolliert eine Notfall-Nachricht
     * 
     * @param string $message Die Nachricht
     * @param array $context Zusätzlicher Kontext
     * @return bool True, wenn die Nachricht protokolliert wurde
     */
    public function emergency(string $message, array $context = []): bool {
        return $this->log(self::LEVEL_EMERGENCY, $message, $context);
    }
    
    /**
     * Registriert einen globalen Exception-Handler
     * 
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function registerExceptionHandler(): Logger {
        set_exception_handler(function(\Throwable $exception) {
            $this->critical(
                get_class($exception) . ': ' . $exception->getMessage(),
                [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ]
            );
        });
        
        return $this;
    }
    
    /**
     * Registriert einen globalen Error-Handler
     * 
     * @return Logger Die aktuelle Instanz für Method Chaining
     */
    public function registerErrorHandler(): Logger {
        set_error_handler(function($level, $message, $file, $line) {
            $levelMap = [
                E_WARNING => self::LEVEL_WARNING,
                E_NOTICE => self::LEVEL_NOTICE,
                E_USER_ERROR => self::LEVEL_ERROR,
                E_USER_WARNING => self::LEVEL_WARNING,
                E_USER_NOTICE => self::LEVEL_NOTICE,
                E_STRICT => self::LEVEL_NOTICE,
                E_RECOVERABLE_ERROR => self::LEVEL_ERROR,
                E_DEPRECATED => self::LEVEL_NOTICE,
                E_USER_DEPRECATED => self::LEVEL_NOTICE,
            ];
            
            $logLevel = $levelMap[$level] ?? self::LEVEL_ERROR;
            
            $this->log($logLevel, $message, [
                'file' => $file,
                'line' => $line,
                'php_error_level' => $level
            ]);
            
            // Standard-PHP-Error-Handler ausführen lassen
            return false;
        });
        
        return $this;
    }
}
/**
<?php
// Logger initialisieren mit minimalem Level WARNING
$logger = new Logger(Logger::LEVEL_WARNING);

// Datei-Handler hinzufügen
$logger->addFileHandler(__DIR__ . '/logs/app.log');

// E-Mail-Handler für kritische Fehler hinzufügen
$logger->addEmailHandler('admin@example.com', Logger::LEVEL_CRITICAL);

// Globalen Kontext hinzufügen
$logger->addContext('app_version', '1.0.0');

if (isset($_SESSION['user_id'])) {
    $logger->addContext('user_id', $_SESSION['user_id']);
}

// Exception- und Error-Handler registrieren
$logger->registerExceptionHandler()
       ->registerErrorHandler();

// Logger verwenden
$logger->info('Benutzer hat sich angemeldet', ['user_id' => 123]);
$logger->warning('Wiederholte fehlgeschlagene Anmeldeversuche', ['ip' => '192.168.1.1', 'attempts' => 5]);
$logger->error('Datenbankverbindung fehlgeschlagen', ['db_host' => 'localhost']);

// Eigenen Handler hinzufügen (z.B. für Slack-Benachrichtigungen)
$logger->addHandler(function($level, $message, $context) {
    if ($level >= Logger::LEVEL_ERROR) {
        // Hier könnte Code zur Slack-Benachrichtigung stehen
    }
});

// Try-Catch mit Logger
try {
    // Potenziell fehleranfälliger Code
    $result = someFunctionThatMightFail();
} catch (Exception $e) {
    $logger->critical($e->getMessage(), [
        'exception_class' => get_class($e),
        'stack_trace' => $e->getTraceAsString()
    ]);
    // Fehlerbehandlung...
}

*/