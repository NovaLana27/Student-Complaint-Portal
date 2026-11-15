<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

/**
 * Base class for every API controller:
 *  - checks the JWT token (unless the action is listed in $publicActions)
 *  - gives helpers: json(), requireRole(), notify(), audit()
 */
abstract class ApiController extends AppController
{
    /** Actions that do not need a login token. */
    protected array $publicActions = [];

    /** The logged-in user (array), filled in by beforeFilter(). */
    protected array $authUser = [];

    public function beforeFilter(EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        if (in_array($this->request->getParam('action'), $this->publicActions, true)) {
            return null;
        }

        $header = $this->request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return $this->json(['message' => 'Authentication required'], 401);
        }

        try {
            $payload = JWT::decode($matches[1], new Key($this->secret(), 'HS256'));
        } catch (Throwable $e) {
            return $this->json(['message' => 'Invalid or expired token'], 401);
        }

        $user = $this->fetchTable('Users')->find()
            ->where(['id' => $payload->sub, 'is_active' => 1])
            ->enableHydration(false)
            ->first();
        if (!$user) {
            return $this->json(['message' => 'Account not found or disabled'], 401);
        }

        unset($user['password_hash']);
        $this->authUser = $user;

        return null;
    }

    protected function secret(): string
    {
        return (string)Configure::readOrFail('Jwt.secret');
    }

    protected function makeToken(array $user): string
    {
        $now = time();

        return JWT::encode([
            'sub' => (int)$user['id'],
            'role' => $user['role'],
            'iat' => $now,
            'exp' => $now + 86400, // 24 hours
        ], $this->secret(), 'HS256');
    }

    /** Send a JSON response. */
    protected function json(array $data, int $status = 200): Response
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody((string)json_encode($data));
    }

    /** Returns a 403 response if the user's role is not allowed, otherwise null. */
    protected function requireRole(array $roles): ?Response
    {
        if (!in_array($this->authUser['role'] ?? '', $roles, true)) {
            return $this->json(['message' => 'You do not have permission to do this'], 403);
        }

        return null;
    }

    /** Save an in-app notification and (if enabled) send an email. */
    protected function notify(int $userId, ?int $complaintId, string $subject, string $message): void
    {
        $user = $this->fetchTable('Users')->find()
            ->where(['id' => $userId])
            ->enableHydration(false)
            ->first();
        if (!$user) {
            return;
        }

        $notifications = $this->fetchTable('Notifications');
        $notifications->save($notifications->newEntity([
            'user_id' => $userId,
            'complaint_id' => $complaintId,
            'channel' => 'in-app',
            'message' => $message,
            'is_read' => 0,
        ], ['validate' => false]));

        if (!Configure::read('Mail.enabled', false)) {
            return;
        }
        try {
            (new Mailer('default'))
                ->setFrom(['no-reply@ru-complaints.local' => 'RU Complaint Portal'])
                ->setTo($user['email'])
                ->setSubject($subject)
                ->deliver($message);
        } catch (Throwable $e) {
            Log::warning('Email failed: ' . $e->getMessage());
        }
    }

    /** Record an action in the audit log. */
    protected function audit(string $action, ?string $entity = null, ?int $entityId = null): void
    {
        $logs = $this->fetchTable('AuditLogs');
        $logs->save($logs->newEntity([
            'user_id' => $this->authUser['id'] ?? null,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'ip_address' => $this->request->clientIp(),
        ], ['validate' => false]));
    }
}