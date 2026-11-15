<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class AuthController extends ApiController
{
    protected array $publicActions = ['register', 'login'];

    /** POST /api/auth/register  (creates a student account) */
    public function register(): Response
    {
        $data = $this->request->getData();
        $errors = [];

        foreach (['full_name', 'email', 'matric_no', 'level', 'department_id', 'password'] as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $errors[$field] = 'This field is required';
            }
        }
        if (!isset($errors['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address';
        }
        // Example format: RUN/IFT/24/17397. Change the pattern if your school's format differs.
        if (!isset($errors['matric_no']) && !preg_match('/^[A-Z]{3}\/[A-Z]{2,5}\/\d{2}\/\d{3,6}$/', strtoupper(trim($data['matric_no'])))) {
            $errors['matric_no'] = 'Use the format RUN/IFT/24/17397';
        }
        if (!isset($errors['level']) && !in_array((int)$data['level'], [100, 200, 300, 400, 500], true)) {
            $errors['level'] = 'Choose a valid level';
        }
        if (!isset($errors['password']) && strlen((string)$data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        if (!isset($errors['department_id']) && !$this->fetchTable('Departments')->exists(['id' => (int)$data['department_id']])) {
            $errors['department_id'] = 'Choose a valid department';
        }
        if ($errors) {
            return $this->json(['message' => 'Validation failed', 'errors' => $errors], 422);
        }

        $users = $this->fetchTable('Users');
        $matric = strtoupper(trim($data['matric_no']));
        $email = strtolower(trim($data['email']));
        if ($users->exists(['email' => $email]) || $users->exists(['matric_no' => $matric])) {
            return $this->json(['message' => 'An account with this email or matric number already exists'], 409);
        }

        $user = $users->newEntity([
            'full_name' => trim($data['full_name']),
            'email' => $email,
            'matric_no' => $matric,
            'level' => (int)$data['level'],
            'department_id' => (int)$data['department_id'],
            'password_hash' => password_hash((string)$data['password'], PASSWORD_DEFAULT),
            'role' => 'student',
            'is_active' => 1,
        ], ['validate' => false]);

        if (!$users->save($user)) {
            return $this->json(['message' => 'Could not create account', 'errors' => $user->getErrors()], 500);
        }

        return $this->json([
            'token' => $this->makeToken($user->toArray()),
            'user' => $this->publicUser($user->toArray()),
        ], 201);
    }

    /** POST /api/auth/login  (email OR matric number + password) */
    public function login(): Response
    {
        $data = $this->request->getData();
        $identifier = trim((string)($data['identifier'] ?? ''));
        $password = (string)($data['password'] ?? '');
        if ($identifier === '' || $password === '') {
            return $this->json(['message' => 'Enter your email or matric number and password'], 422);
        }

        $user = $this->fetchTable('Users')->find()
            ->where(['OR' => ['email' => strtolower($identifier), 'matric_no' => strtoupper($identifier)]])
            ->enableHydration(false)
            ->first();

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            return $this->json(['message' => 'Incorrect login details'], 401);
        }

        $this->authUser = $user;
        $this->audit('login', 'users', (int)$user['id']);

        return $this->json(['token' => $this->makeToken($user), 'user' => $this->publicUser($user)]);
    }

    /** GET /api/auth/me */
    public function me(): Response
    {
        return $this->json(['user' => $this->publicUser($this->authUser)]);
    }

    private function publicUser(array $user): array
    {
        return [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'matric_no' => $user['matric_no'] ?? null,
            'level' => $user['level'] ?? null,
            'department_id' => $user['department_id'] ?? null,
            'unit_id' => $user['unit_id'] ?? null,
            'role' => $user['role'],
        ];
    }
}