<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Account Model
 * Handles user accounts, authentication, and profile management
 */
class Account extends BaseModel
{
    protected $table = 'accounts';

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        try {
            // Check if email already exists
            if ($this->findWhere('email', $data['email'])) {
                return ['success' => false, 'message' => 'Email này đã được sử dụng.'];
            }

            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $userId = $this->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role' => 'customer'
            ]);

            return ['success' => true, 'message' => 'Đăng ký tài khoản thành công!', 'user_id' => $userId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể đăng ký: ' . $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, role, email, password 
                                         FROM accounts 
                                         WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công!',
                    'user' => $user,
                    'redirect' => $this->getRedirectUrl($user['role'])
                ];
            }

            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi đăng nhập: ' . $e->getMessage()];
        }
    }

    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role): string
    {
        if ($role === 'admin' || $role === 'employee') {
            return '/admin/index.php';
        }
        return '/';
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email)
    {
        return $this->findWhere('email', $email);
    }

    /**
     * Update user profile
     */
    public function updateProfile(int $id, array $data): bool
    {
        try {
            return $this->update($id, [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Change password
     */
    public function changePassword(int $id, string $oldPassword, string $newPassword): array
    {
        try {
            $user = $this->find($id);

            if (!$user) {
                return ['success' => false, 'message' => 'Người dùng không tồn tại.'];
            }

            // Verify old password
            if (!password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Mật khẩu cũ không đúng.'];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $this->update($id, ['password' => $hashedPassword]);

            return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi khi đổi mật khẩu: ' . $e->getMessage()];
        }
    }

    /**
     * Get current user role
     */
    public function getCurrentRole(): ?string
    {
        $accountId = $_SESSION['id'] ?? null;
        if (!$accountId) {
            return null;
        }

        $user = $this->find($accountId);
        return $user ? $user['role'] : null;
    }

    /**
     * Check if user has specific role(s)
     */
    public function hasRole($roles): bool
    {
        if (!isLoggedIn()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $currentRole = $this->getCurrentRole();
        return in_array($currentRole, $roles);
    }
}
