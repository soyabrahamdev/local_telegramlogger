<?php
namespace local_telegramlogger;

defined('MOODLE_INTERNAL') || die();

class telegram {

    public static function send(string $message): bool {
        $token  = get_config('local_telegramlogger', 'bottoken');
        $chatid = get_config('local_telegramlogger', 'chatid');

        if (empty($token) || empty($chatid)) {
            return false;
        }

        if (mb_strlen($message) > 3900) {
            $message = mb_substr($message, 0, 3900) . "...\n[truncado]";
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id'    => $chatid,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ];

        // Se usa el wrapper \curl de Moodle: respeta el proxy configurado en el sitio.
        $curl = new \curl();
        $curl->setopt(['CURLOPT_TIMEOUT' => 8, 'CURLOPT_CONNECTTIMEOUT' => 5]);
        $curl->post($url, $payload);

        $info = $curl->get_info();
        return isset($info['http_code']) && (int)$info['http_code'] === 200;
    }
}