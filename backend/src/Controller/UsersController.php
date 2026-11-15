<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/** Admin-only user management (create officers, change roles, disable accounts). */
class UsersController extends ApiController
{
    /** GET /api/users?role= */
    public function index(): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $query = $this->fetchTable('Users')->find()
            ->select(['id', 'full_name', 'email', 'matric_no', 'level', 'department_id', 'unit_id', 'role', 'is_active', 'created'])
            ->orderBy(['id' => 'DESC'])
            ->enableHydration(false);
        $role = $this->request->getQuery('role');
        if ($role && in_array($role, ['student', 'officer', 'admin'], true)) {
            $query->where(['role' => $role]);
        }

        return $this->json(['data' => $query->all()->toList()]);
    }

    /** POST /api/users  body: full_name, email, password, role (officer|admin), unit_id */
    public function add(): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $data = $this->request->getData();
        $errors = [];
        if (trim((string)($data['full_name'] ?? '')) === '') {
            $errors['full_name'] = 'Required';
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email';
        }
        if (strlen((string)($data['password'] ?? '')) < 8) {
            $errors['password'] = 'At least 8 characters';
        }
        $role = $data['role'] ?? 'officer';
        if (!in_array($role, ['officer', 'admin'], true)) {
            $errors['role'] = 'Role must be officer or admin';
        }
        if ($role === 'officer' && !$this->fetchTable('Units')->exists(['id' => (int)($data['unit_id'] ?? 0)])) {
            $errors['unit_id'] = 'Officers must belong to a unit';
        }
        if ($errors) {
            return $this->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        }

        $users = $this->fetchTable('Users');
        $email = strtolower(trim($data['email']));
        if ($users->exists(['email' => $email])) {
            return $this->json(['message' => 'Email already in use'], 409);
        }

        $user = $users->newEntity([
            'full_name' => trim($data['full_name']),
            'email' => $email,
            'password_hash' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
            'role' => $role,
            'unit_id' => $role === 'officer' ? (int)$data['unit_id'] : null,
            'is_active' => 1,
        ], ['validate' => false]);
        if (!$users->save($user)) {
            return $this->json(['message' => 'Could not create user', 'errors' => $user->getErrors()], 500);
        }
        $this->audit('user_created', 'users', (int)$user->id);

        return $this->json(['id' => (int)$user->id, 'message' => 'User created'], 201);
    }

    /** PATCH /api/users/{id}  body: role, unit_id, is_active */
    public function update(string $id): Response
    {
        if ($denied = $this->requireRole(['admin'])) {
            return $denied;
        }
        $users = $this->fetchTable('Users');
        if (!$users->exists(['id' => (int)$id])) {
            return $this->json(['message' => 'User not found'], 404);
        }
        $user = $users->get((int)$id);
        $data = $this->request->getData();

        if (isset($data['role'])) {
            if (!in_array($data['role'], ['student', 'officer', 'admin'], true)) {
                return $this->json(['message' => 'Invalid role'], 422);
            }
            $user->role = $data['role'];
        }
        if (array_key_exists('unit_id', $data)) {
            $user->unit_id = $data['unit_id'] ? (int)$data['unit_id'] : null;
        }
        if (isset($data['is_active'])) {
            if ((int)$id === (int)$this->authUser['id']) {
                return $this->json(['message' => 'You cannot disable your own account'], 422);
            }
            $user->is_active = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        }
        $users->save($user);
        $this->audit('user_updated', 'users', (int)$id);

        return $this->json(['message' => 'User updated']);
    }
}