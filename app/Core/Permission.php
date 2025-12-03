<?php

/**
 * Permission Management System
 * Centralized role-based access control
 */
class Permission
{
    // Permission Constants
    const VIEW_DASHBOARD = 'view_dashboard';
    const VIEW_REPORTS = 'view_reports';
    const EXPORT_DATA = 'export_data';
    
    const VIEW_PRODUCTS = 'view_products';
    const MANAGE_PRODUCTS = 'manage_products';
    const DELETE_PRODUCTS = 'delete_products';
    
    const VIEW_ORDERS = 'view_orders';
    const VIEW_ALL_ORDERS = 'view_all_orders';
    const MANAGE_ORDERS = 'manage_orders';
    
    const VIEW_CATEGORIES = 'view_categories';
    const MANAGE_CATEGORIES = 'manage_categories';
    
    const VIEW_ACCOUNTS = 'view_accounts';
    const MANAGE_ACCOUNTS = 'manage_accounts';
    
    const VIEW_VOUCHERS = 'view_vouchers';
    const MANAGE_VOUCHERS = 'manage_vouchers';
    
    const ADD_TO_CART = 'add_to_cart';
    const CHECKOUT = 'checkout';

    /**
     * Role permission mappings
     */
    private static $rolePermissions = [
        'admin' => [
            // Admin has ALL permissions
            self::VIEW_DASHBOARD,
            self::VIEW_REPORTS,
            self::EXPORT_DATA,
            self::VIEW_PRODUCTS,
            self::MANAGE_PRODUCTS,
            self::DELETE_PRODUCTS,
            self::VIEW_ORDERS,
            self::VIEW_ALL_ORDERS,
            self::MANAGE_ORDERS,
            self::VIEW_CATEGORIES,
            self::MANAGE_CATEGORIES,
            self::VIEW_ACCOUNTS,
            self::MANAGE_ACCOUNTS,
            self::VIEW_VOUCHERS,
            self::MANAGE_VOUCHERS,
        ],
        'employee' => [
            // Employee: Can manage products/orders, view reports
            self::VIEW_DASHBOARD,
            self::VIEW_REPORTS,
            self::VIEW_PRODUCTS,
            self::MANAGE_PRODUCTS,
            // NO DELETE_PRODUCTS
            self::VIEW_ORDERS,
            self::VIEW_ALL_ORDERS,
            self::MANAGE_ORDERS,
            self::VIEW_CATEGORIES,
            // NO MANAGE_CATEGORIES
            // NO account/voucher management
        ],
        'customer' => [
            // Customer: Shop and view own orders
            self::VIEW_PRODUCTS,
            self::VIEW_ORDERS, // Own orders only
            self::ADD_TO_CART,
            self::CHECKOUT,
        ],
        'guest' => [
            // Guest: View only
            self::VIEW_PRODUCTS,
        ],
    ];

    /**
     * Check if current user has a specific permission
     */
    public static function can(string $permission): bool
    {
        $role = self::getCurrentRole();
        
        // Admin has all permissions (wildcard)
        if ($role === 'admin') {
            return true;
        }
        
        if (!isset(self::$rolePermissions[$role])) {
            return false;
        }
        
        return in_array($permission, self::$rolePermissions[$role]);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        return self::getCurrentRole() === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        return in_array(self::getCurrentRole(), $roles);
    }

    /**
     * Require specific permission or throw exception
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::can($permission)) {
            $role = self::getCurrentRole();
            http_response_code(403);
            die("Access Denied: You don't have permission to '{$permission}' (Role: {$role})");
        }
    }

    /**
     * Require specific role or redirect
     */
    public static function requireRole(string $role): void
    {
        if (!self::hasRole($role)) {
            self::accessDenied();
        }
    }

    /**
     * Require any of the given roles
     */
    public static function requireAnyRole(array $roles): void
    {
        if (!self::hasAnyRole($roles)) {
            self::accessDenied();
        }
    }

    /**
     * Get current user's role
     */
    public static function getCurrentRole(): string
    {
        if (!isset($_SESSION['id'])) {
            return 'guest';
        }
        
        return strtolower($_SESSION['role'] ?? 'customer');
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['id']);
    }

    /**
     * Check if user is admin or employee (staff)
     */
    public static function isStaff(): bool
    {
        return self::hasAnyRole(['admin', 'employee']);
    }

    /**
     * Handle access denied
     */
    private static function accessDenied(): void
    {
        $role = self::getCurrentRole();
        
        if ($role === 'guest') {
            // Redirect to login
            setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: /auth/login');
            exit;
        }
        
        // Logged in but wrong role
        setFlashMessage('error', 'Bạn không có quyền truy cập trang này.');
        header('Location: /');
        exit;
    }

    /**
     * Get permissions for a role
     */
    public static function getRolePermissions(string $role): array
    {
        return self::$rolePermissions[$role] ?? [];
    }

    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return ['admin', 'employee', 'customer', 'guest'];
    }
}
