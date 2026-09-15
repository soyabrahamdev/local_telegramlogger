<?php
defined('MOODLE_INTERNAL') || die();

if (!empty(get_config('local_telegramlogger', 'enabled'))) {

    $previousexceptionhandler = set_exception_handler(function (\Throwable $e) use (&$previousexceptionhandler) {
        local_telegramlogger_queue_error(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
        if (is_callable($previousexceptionhandler)) {
            call_user_func($previousexceptionhandler, $e);
        }
    });

    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            local_telegramlogger_queue_error('Fatal error', $error['message'], $error['file'], $error['line']);
        }
    });
}

function local_telegramlogger_queue_error(string $type, string $message, string $file = '', int $line = 0): void {
    global $DB, $FULLME, $USER;

    try {
        $record = new stdClass();
        $record->timecreated = time();
        $record->errortype   = $type;
        $record->message     = $message;
        $record->errfile     = $file;
        $record->errline     = $line;
        $record->url         = $FULLME ?? '';
        $record->userid      = isset($USER->id) ? $USER->id : null;
        $record->sent        = 0;
        $DB->insert_record('local_telegramlogger_queue', $record);
    } catch (\Throwable $ignore) {
        // Nunca lances un error dentro del propio manejador de errores.
    }
}