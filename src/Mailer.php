<?php

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envoi des emails via SMTP (PHPMailer).
 *
 * Si aucun serveur SMTP n'est configuré (développement local), les mails sont écrits
 * dans storage/mails/ : on peut les relire sans risquer d'envoyer de vrais emails.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $template, array $data = [], ?string $replyTo = null): bool
    {
        $html = render_partial('emails/' . $template, $data + ['subject' => $subject]);

        if (Env::get('MAIL_HOST') === '') {
            $file = dirname(__DIR__) . '/storage/mails/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.html';
            file_put_contents($file, "<!-- To: $to | Subject: $subject -->\n" . $html);
            return true;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = Env::get('MAIL_HOST');
            $mail->Port = (int) Env::get('MAIL_PORT', '587');
            $mail->SMTPAuth = true;
            $mail->Username = Env::get('MAIL_USER');
            $mail->Password = Env::get('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom(Env::get('MAIL_FROM', 'no-reply@vite-gourmand.fr'), 'Vite & Gourmand');
            $mail->addAddress($to);
            if ($replyTo !== null) {
                $mail->addReplyTo($replyTo);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = trim(html_entity_decode(strip_tags($html)));
            $mail->send();
            return true;
        } catch (MailException $e) {
            // Un mail non envoyé ne doit pas bloquer la commande : on journalise l'erreur
            error_log('[Mailer] ' . $e->getMessage());
            return false;
        }
    }

    public static function entreprise(): string
    {
        return Env::get('MAIL_ENTREPRISE', 'contact@vite-gourmand.fr');
    }
}
