<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class NotificationsController extends ApiController
{
    /** GET /api/notifications  (the logged-in user's latest 50) */
    public function index(): Response
    {
        $rows = $this->fetchTable('Notifications')->find()
            ->where(['user_id' => (int)$this->authUser['id']])
            ->orderBy(['id' => 'DESC'])
            ->limit(50)
            ->enableHydration(false)->all()->toList();

        return $this->json(['data' => $rows]);
    }

    /** PATCH /api/notifications/{id}/read */
    public function markRead(string $id): Response
    {
        $this->fetchTable('Notifications')->updateAll(
            ['is_read' => 1],
            ['id' => (int)$id, 'user_id' => (int)$this->authUser['id']]
        );

        return $this->json(['message' => 'Marked as read']);
    }
}