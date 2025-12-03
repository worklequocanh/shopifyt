<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * Voucher Model
 * Handles discount vouchers
 */
class Voucher extends BaseModel
{
    protected $table = 'vouchers';

    /**
     * Get voucher by code
     */
    public function getByCode(string $code)
    {
        return $this->findWhere('code', $code);
    }

    /**
     * Validate voucher
     */
    public function validate(string $code, float $orderAmount): array
    {
        $voucher = $this->getByCode($code);

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        }

        if (!$voucher['is_active']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hiệu lực.'];
        }

        // Check dates
        $now = date('Y-m-d H:i:s');
        if ($now < $voucher['start_date'] || $now > $voucher['end_date']) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa bắt đầu hoặc đã hết hạn.'];
        }

        // Check usage limit
        if (!is_null($voucher['usage_limit']) && $voucher['used_count'] >= $voucher['usage_limit']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        // Check minimum order value
        if ($orderAmount < $voucher['min_order_value']) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng tối thiểu ' . format_currency($voucher['min_order_value']) . ' để sử dụng mã này.'
            ];
        }

        return ['valid' => true, 'voucher' => $voucher];
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount(array $voucher, float $orderAmount): float
    {
        $discount = 0;

        if ($voucher['discount_type'] === 'percentage') {
            $discount = ($orderAmount * $voucher['discount_value']) / 100;
            
            // Apply max discount if set
            if ($voucher['max_discount'] > 0 && $discount > $voucher['max_discount']) {
                $discount = $voucher['max_discount'];
            }
        } else {
            // Fixed discount
            $discount = $voucher['discount_value'];
        }

        return min($discount, $orderAmount);
    }

    /**
     * Get all active vouchers
     */
    public function getActive(): array
    {
        $now = date('Y-m-d');
        
        $sql = "SELECT * FROM vouchers 
                WHERE is_active = 1 
                AND start_date <= ? 
                AND end_date >= ?
                ORDER BY discount_value DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$now, $now]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get all vouchers
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Check if voucher code exists
     */
    public function exists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE code = ?";
        $params = [$code];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
