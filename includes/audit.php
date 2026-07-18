<?php
declare(strict_types=1);

/*
| Records important activities for the audit log report.
*/

function log_activity(?int $userId, string $username, string $action): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO audit_logs (user_id, username, action, ip_address, created_at)
             VALUES (:user_id, :username, :action, :ip_address, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'username' => $username,
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'local',
        ]);
    } catch (Throwable $exception) {
        error_log('Audit log failed: ' . $exception->getMessage());
    }
}

