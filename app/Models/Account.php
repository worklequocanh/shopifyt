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
            
            // Generate verification token (64 chars hex)
            require_once __DIR__ . '/../Helpers/email_helpers.php';
            $token = generateToken(32);
            $hashedToken = hashToken($token);
            
            // Token expires in 24 hours
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            // Insert user with verification token
            $userId = $this->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role' => 'customer',
                'email_verified' => 0,
                'verification_token' => $hashedToken,
                'verification_expires' => $expiresAt
            ]);
            
            // Send verification email
            $user = [
                'id' => $userId,
                'name' => $data['name'],
                'email' => $data['email']
            ];
            
            $emailService = getEmailService();
            $emailSent = $emailService->sendVerification($user, $token);
            
            if ($emailSent) {
                error_log("Verification email sent to: {$data['email']}");
            } else {
                error_log("Failed to send verification email to: {$data['email']}");
            }

            return [
                'success' => true, 
                'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác nhận tài khoản.', 
                'user_id' => $userId
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Không thể đăng ký: ' . $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login(string $email, string $password): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, role, email, password, email_verified 
                                         FROM accounts 
                                         WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
            }

            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
            }

            // Remove password from user data
            unset($user['password']);

            return [
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'user' => $user
            ];
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
     * Verify email with token
     */
    public function verifyEmail(string $token): array
    {
        try {
            require_once __DIR__ . '/../Helpers/email_helpers.php';
            $hashedToken = hashToken($token);
            
            // Find user with this token
            $stmt = $this->pdo->prepare(
                "SELECT * FROM accounts 
                 WHERE verification_token = ? 
                 AND verification_expires > NOW()
                 LIMIT 1"
            );
            $stmt->execute([$hashedToken]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn.'];
            }
            
            if ($user['email_verified']) {
                return ['success' => false, 'message' => 'Email đã được xác nhận trước đó.'];
            }
            
            // Mark as verified
            $this->update($user['id'], [
                'email_verified' => 1,
                'verification_token' => null,
                'verification_expires' => null
            ]);
            
            return [
                'success' => true, 
                'message' => 'Xác nhận email thành công! Bạn có thể đăng nhập ngay.',
                'user' => $user
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi xác nhận: ' . $e->getMessage()];
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): array
    {
        try {
            $user = $this->findWhere('email', $email);
            
            if (!$user) {
                // Don't reveal if email exists (security)
                return ['success' => true, 'message' => 'Nếu email tồn tại, link đặt lại mật khẩu đã được gửi.'];
            }
            
            require_once __DIR__ . '/../Helpers/email_helpers.php';
            $token = generateToken(32);
            $hashedToken = hashToken($token);
            
            // Token expires in 1 hour
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset token
            $this->update($user['id'], [
                'reset_token' => $hashedToken,
                'reset_expires' => $expiresAt
            ]);
            
            // Send reset email
            $emailService = getEmailService();
            $emailService->sendPasswordReset($user, $token);
            
            return ['success' => true, 'message' => 'Nếu email tồn tại, link đặt lại mật khẩu đã được gửi.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        try {
            require_once __DIR__ . '/../Helpers/email_helpers.php';
            $hashedToken = hashToken($token);
            
            // Find user with valid reset token
            $stmt = $this->pdo->prepare(
                "SELECT * FROM accounts 
                 WHERE reset_token = ? 
                 AND reset_expires > NOW()
                 LIMIT 1"
            );
            $stmt->execute([$hashedToken]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn.'];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $this->update($user['id'], [
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_expires' => null
            ]);
            
            return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
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
