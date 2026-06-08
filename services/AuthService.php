<?php

require_once __DIR__ . '/MailService.php';

class AuthService
{
    /**
     * Helper to verify if an IP/Identifier is rate limited.
     */
    private static function checkRateLimit(PDO $db, string $identifier, string $action, int $maxAttempts = 5, int $lockoutMinutes = 15): array
    {
        try {
            $stmt = $db->prepare('SELECT id, attempts, last_attempt FROM rate_limits WHERE identifier = ? AND action = ?');
            $stmt->execute([$identifier, $action]);
            $row = $stmt->fetch();

            if ($row) {
                $attempts = (int) $row['attempts'];
                $lastAttempt = strtotime($row['last_attempt']);
                $timeLeft = ($lastAttempt + ($lockoutMinutes * 60)) - time();

                if ($timeLeft > 0) {
                    if ($attempts >= $maxAttempts) {
                        return ['allowed' => false, 'time_left' => ceil($timeLeft / 60)];
                    }
                } else {
                    // Reset attempt window since time expired
                    $db->prepare('UPDATE rate_limits SET attempts = 0 WHERE id = ?')->execute([$row['id']]);
                }
            }
        } catch (Throwable $e) {
            // Safe fallback
        }
        return ['allowed' => true];
    }

    /**
     * Record a failed attempt or increment lockout counter.
     */
    private static function incrementRateLimit(PDO $db, string $identifier, string $action): void
    {
        try {
            $stmt = $db->prepare('SELECT id, attempts, last_attempt FROM rate_limits WHERE identifier = ? AND action = ?');
            $stmt->execute([$identifier, $action]);
            $row = $stmt->fetch();

            if ($row) {
                $db->prepare('UPDATE rate_limits SET attempts = attempts + 1, last_attempt = NOW() WHERE id = ?')
                   ->execute([$row['id']]);
            } else {
                $db->prepare('INSERT INTO rate_limits (identifier, action, attempts, last_attempt) VALUES (?, ?, 1, NOW())')
                   ->execute([$identifier, $action]);
            }
        } catch (Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Clear active rate limits upon successful login/validation.
     */
    private static function clearRateLimit(PDO $db, string $identifier, string $action): void
    {
        try {
            $db->prepare('DELETE FROM rate_limits WHERE identifier = ? AND action = ?')->execute([$identifier, $action]);
        } catch (Throwable $e) {
            // Safe fallback
        }
    }

    public static function generateOtp(PDO $db, int $userId, string $purpose): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $db->prepare(
            'UPDATE otp_verifications SET is_used = 1 WHERE user_id = ? AND purpose = ? AND is_used = 0'
        );
        $stmt->execute([$userId, $purpose]);

        $stmt = $db->prepare(
            'INSERT INTO otp_verifications (user_id, otp_code, purpose, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))'
        );
        $stmt->execute([$userId, $otp, $purpose]);
        return $otp;
    }

    public static function getActiveOtp(PDO $db, int $userId, string $purpose): ?string
    {
        $stmt = $db->prepare(
            'SELECT otp_code FROM otp_verifications
             WHERE user_id = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$userId, $purpose]);
        $row = $stmt->fetch();
        return $row ? (string) $row['otp_code'] : null;
    }

    public static function verifyOtp(PDO $db, int $userId, string $otp, string $purpose): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $rateKey = "otp_{$userId}_{$ip}";

        // Max 3 OTP attempts, lockout for 10 minutes
        $limit = self::checkRateLimit($db, $rateKey, 'verify_otp', 3, 10);
        if (!$limit['allowed']) {
            return false;
        }

        $otp = trim($otp);
        if (!preg_match('/^\d{6}$/', $otp)) {
            self::incrementRateLimit($db, $rateKey, 'verify_otp');
            return false;
        }

        $stmt = $db->prepare(
            'SELECT id FROM otp_verifications
             WHERE user_id = ? AND otp_code = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$userId, $otp, $purpose]);
        $row = $stmt->fetch();

        if (!$row) {
            self::incrementRateLimit($db, $rateKey, 'verify_otp');
            return false;
        }

        $stmt = $db->prepare('UPDATE otp_verifications SET is_used = 1 WHERE id = ?');
        $stmt->execute([$row['id']]);

        self::clearRateLimit($db, $rateKey, 'verify_otp');
        return true;
    }

    public static function register(PDO $db, string $fullName, string $email, string $password, string $roleSlug): array
    {
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $stmt = $db->prepare('SELECT id FROM roles WHERE slug = ?');
        $stmt->execute([$roleSlug]);
        $role = $stmt->fetch();
        if (!$role) {
            return ['success' => false, 'message' => 'Invalid role selected.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $status = $roleSlug === 'recruiter' ? 'pending' : 'pending';

        $stmt = $db->prepare(
            'INSERT INTO users (role_id, email, password, full_name, status) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$role['id'], $email, $hash, $fullName, $status]);
        $userId = (int) $db->lastInsertId();

        if ($roleSlug === 'jobseeker') {
            $db->prepare('INSERT INTO job_seeker_profiles (user_id) VALUES (?)')->execute([$userId]);
        }

        $otp = self::generateOtp($db, $userId, 'email_verify');
        MailService::sendOtp($email, $fullName, $otp, 'email_verify');

        return ['success' => true, 'user_id' => $userId, 'otp' => (defined('DEV_MODE') && DEV_MODE) ? $otp : null];
    }

    public static function login(PDO $db, string $email, string $password): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown_ip';
        $rateKey = "login_{$ip}";

        // Max 5 attempts, lockout for 15 minutes
        $limit = self::checkRateLimit($db, $rateKey, 'login', 5, 15);
        if (!$limit['allowed']) {
            return ['success' => false, 'message' => "Too many failed attempts. Locked out for {$limit['time_left']} minute(s)."];
        }

        $stmt = $db->prepare(
            'SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            self::incrementRateLimit($db, $rateKey, 'login');
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if ($user['status'] === 'blocked') {
            return ['success' => false, 'message' => 'Your account has been blocked. Contact support.'];
        }
        if (!$user['email_verified']) {
            return ['success' => false, 'message' => 'Please verify your email first.', 'needs_otp' => true, 'user_id' => $user['id']];
        }
        if ($user['role_slug'] === 'recruiter' && $user['status'] === 'pending') {
            return ['success' => false, 'message' => 'Your recruiter account is pending admin approval.'];
        }

        self::clearRateLimit($db, $rateKey, 'login');
        session_regenerate_id(true);
        loadUserSession($db, (int) $user['id']);

        $stmt = $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$user['id']]);
        logActivity($db, (int) $user['id'], 'login', 'User logged in');

        return ['success' => true];
    }
}
