<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/EmailTemplateService.php';

class MailService
{
    /**
     * Fetch SMTP configuration from the database (app_settings table)
     * with fallback to constants defined in config/app.php.
     */
    private static function getSmtpConfig(): array
    {
        $config = [
            'host' => defined('SMTP_HOST') ? SMTP_HOST : '',
            'port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
            'user' => defined('SMTP_USER') ? SMTP_USER : '',
            'pass' => defined('SMTP_PASS') ? SMTP_PASS : '',
            'from' => defined('SMTP_FROM') ? SMTP_FROM : 'noreply@smarthire.pro',
            'from_name' => defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartHire Pro',
        ];

        try {
            require_once BASE_PATH . '/config/database.php';
            $db = Database::getConnection();
            
            // Check if app_settings table exists
            $stmt = $db->query("SHOW TABLES LIKE 'app_settings'");
            if ($stmt->fetch()) {
                $settings = $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'smtp_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
                
                if (!empty($settings['smtp_host'])) $config['host'] = $settings['smtp_host'];
                if (!empty($settings['smtp_port'])) $config['port'] = (int)$settings['smtp_port'];
                if (!empty($settings['smtp_user'])) $config['user'] = $settings['smtp_user'];
                if (!empty($settings['smtp_pass'])) $config['pass'] = $settings['smtp_pass'];
                if (!empty($settings['smtp_from'])) $config['from'] = $settings['smtp_from'];
                if (!empty($settings['smtp_from_name'])) $config['from_name'] = $settings['smtp_from_name'];
            }
        } catch (Throwable $e) {
            // Suppress and fallback to hardcoded configs
        }

        return $config;
    }

    /**
     * Core sending logic using PHPMailer or logging fallback.
     */
    private static function send(string $email, string $name, string $subject, string $htmlBody): bool
    {
        // 1. Log locally if DEV_MODE or fallback logging is active
        $logDir = BASE_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logLine = date('Y-m-d H:i:s') . " | TO: {$email} ({$name}) | SUBJECT: {$subject}\n---\n{$htmlBody}\n=========================================\n";
        file_put_contents($logDir . '/mail.log', $logLine, FILE_APPEND);

        if (defined('DEV_MODE') && DEV_MODE) {
            return true;
        }

        // 2. Attempt production SMTP send if vendor auto-loader exists
        if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
            error_log("PHPMailer autoload.php not found. Email logged to logs/mail.log");
            return true; // Return true as we successfully logged it in dev/fallback
        }

        require_once BASE_PATH . '/vendor/autoload.php';
        
        $config = self::getSmtpConfig();
        if (empty($config['host']) || empty($config['user'])) {
            error_log("SMTP credentials not set. Email logged to logs/mail.log");
            return true;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['user'];
            $mail->Password = $config['pass'];
            
            // Detect security based on port
            if ($config['port'] === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $config['port'];
            
            $mail->setFrom($config['from'], $config['from_name']);
            $mail->addAddress($email, $name);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail error sending to {$email}: " . $e->getMessage());
            return false;
        }
    }

    public static function sendOtp(string $email, string $name, string $otp, string $purposeText = 'Verification'): bool
    {
        $subject = "SmartHire Pro - Your OTP Code: {$otp}";
        $html = EmailTemplateService::getOtpTemplate($name, $otp, $purposeText);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendWelcome(string $email, string $name, string $role): bool
    {
        $subject = "Welcome to SmartHire Pro!";
        $html = EmailTemplateService::getWelcomeTemplate($name, $role);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendApplicationSubmitted(string $email, string $name, string $jobTitle, string $companyName): bool
    {
        $subject = "Application Submitted: {$jobTitle}";
        $html = EmailTemplateService::getApplicationSubmittedTemplate($name, $jobTitle, $companyName);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendShortlisted(string $email, string $name, string $jobTitle, string $companyName): bool
    {
        $subject = "Congratulations! You are shortlisted for {$jobTitle}";
        $html = EmailTemplateService::getShortlistedTemplate($name, $jobTitle, $companyName);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendInterviewScheduled(
        string $email,
        string $name,
        string $jobTitle,
        string $companyName,
        string $dateTime,
        string $type,
        string $link,
        string $instructions
    ): bool {
        $subject = "Interview Scheduled: {$jobTitle} at {$companyName}";
        $html = EmailTemplateService::getInterviewScheduledTemplate($name, $jobTitle, $companyName, $dateTime, $type, $link, $instructions);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendSelected(string $email, string $name, string $jobTitle, string $companyName): bool
    {
        $subject = "Congratulations! Job Offer for {$jobTitle}";
        $html = EmailTemplateService::getSelectedTemplate($name, $jobTitle, $companyName);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendRejected(string $email, string $name, string $jobTitle, string $companyName): bool
    {
        $subject = "Application Update: {$jobTitle}";
        $html = EmailTemplateService::getRejectedTemplate($name, $jobTitle, $companyName);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendRecruiterApproved(string $email, string $name, string $companyName): bool
    {
        $subject = "SmartHire Pro - Recruiter Account Approved";
        $html = EmailTemplateService::getRecruiterApprovedTemplate($name, $companyName);
        return self::send($email, $name, $subject, $html);
    }

    public static function sendRecruiterRejected(string $email, string $name, string $companyName, string $reason = ''): bool
    {
        $subject = "SmartHire Pro - Recruiter Account Update";
        $html = EmailTemplateService::getRecruiterRejectedTemplate($name, $companyName, $reason);
        return self::send($email, $name, $subject, $html);
    }
}
