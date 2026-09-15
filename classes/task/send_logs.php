<?php
namespace local_telegramlogger\task;

defined('MOODLE_INTERNAL') || die();

class send_logs extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('tasksendlogs', 'local_telegramlogger');
    }

    public function execute() {
        global $DB;

        if (empty(get_config('local_telegramlogger', 'enabled'))) {
            return;
        }

        $records = $DB->get_records('local_telegramlogger_queue', ['sent' => 0], 'id ASC', '*', 0, 20);
        if (!$records) {
            return;
        }

        foreach ($records as $record) {
            $ok = \local_telegramlogger\telegram::send($this->format_message($record));

            if ($ok) {
                $record->sent = 1;
                $record->timesent = time();
                $DB->update_record('local_telegramlogger_queue', $record);
            }
            // Si falla el envío, se deja pendiente para reintentar en la siguiente ejecución.
        }

        // Limpieza de registros ya enviados con más de 30 días.
        $DB->delete_records_select(
            'local_telegramlogger_queue',
            'sent = 1 AND timesent < :cutoff',
            ['cutoff' => time() - 30 * DAYSECS]
        );
    }

    private function format_message(\stdClass $r): string {
        $date = userdate($r->timecreated, '%d-%m-%Y %H:%M:%S');
        $msg  = "🔴 *Error en Moodle*\n";
        $msg .= "*Fecha:* {$date}\n";
        $msg .= "*Tipo:* {$r->errortype}\n";
        $msg .= "*Mensaje:* " . substr($r->message, 0, 500) . "\n";
        if (!empty($r->errfile)) {
            $msg .= "*Archivo:* {$r->errfile}";
            $msg .= !empty($r->errline) ? " (línea {$r->errline})\n" : "\n";
        }
        if (!empty($r->url)) {
            $msg .= "*URL:* {$r->url}\n";
        }
        return $msg;
    }
}