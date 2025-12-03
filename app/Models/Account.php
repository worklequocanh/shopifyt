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
    private function getRedirectUrl(string $role): string
    {
        if ($role === 'admin' || $role === 'employee') {
            return '/admin/dashboard';
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

    /**
     * Get all accounts with filters
     */
    public function getAllAccounts($role = 'all', $search = ''): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($role !== 'all') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create new account (admin)
     */
    public function createAccount(array $data): bool
    {
        try {
            // Hash password
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $this->create($data);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update account (admin)
     */
    public function updateAccount(int $id, array $data): bool
    {
        try {
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }
            
            return $this->update($id, $data);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): bool
    {
        $account = $this->find($id);
        if (!$account) return false;

        $newStatus = $account['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Count customers
     */
    public function countCustomers(): int
    {
        return $this->count("role = 'customer'");
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
