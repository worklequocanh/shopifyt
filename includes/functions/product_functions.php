<?php

$colorMap = [
    'Đỏ'           => 'bg-red-600',
    'Xanh dương'    => 'bg-blue-600',
    'Xanh lá'      => 'bg-green-500',
    'Đen'          => 'bg-black',
    'Trắng'        => 'bg-white',
    'Vàng'         => 'bg-yellow-400',
    'Cam'          => 'bg-orange-500',
    'Hồng'         => 'bg-pink-500',
    'Tím'          => 'bg-purple-600',
    'Xám'          => 'bg-gray-500',
    'Nâu'          => 'bg-amber-800',
    // Thêm các màu khác nếu cần
];

function countAllProducts(PDO $pdo): int
{
    $stmt = $pdo->query("SELECT COUNT(id) FROM products WHERE is_active = 1");
    return (int)$stmt->fetchColumn();
}

function getAllProducts(PDO $pdo, int $limit = 12, int $offset = 0): array
{
    $sql = "SELECT 
                p.*, 
                pi.image_url AS main_image
            FROM 
                products AS p
            LEFT JOIN 
                product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
            WHERE 
                p.is_active = TRUE
            ORDER BY 
                p.created_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductDetails(PDO $pdo, int $productId): ?array
{
    // Mảng kết quả cuối cùng
    $result = [
        'colors' => [],
        'sizes' => [],
        'variants' => []
    ];

    try {
        // ---- 2. Lấy danh sách các MÀU SẮC có sẵn của sản phẩm ----
        $sqlColors = "
            SELECT DISTINCT av.id, av.value
            FROM product_variants pv
            JOIN variant_values vv ON pv.id = vv.variant_id
            JOIN attribute_values av ON vv.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pv.product_id = :productId AND a.name = 'Màu sắc'
        ";
        $stmtColors = $pdo->prepare($sqlColors);
        $stmtColors->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmtColors->execute();
        $result['colors'] = $stmtColors->fetchAll(PDO::FETCH_ASSOC);


        // ---- 3. Lấy danh sách các KÍCH CỠ có sẵn của sản phẩm ----
        $sqlSizes = "
            SELECT DISTINCT av.id, av.value
            FROM product_variants pv
            JOIN variant_values vv ON pv.id = vv.variant_id
            JOIN attribute_values av ON vv.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pv.product_id = :productId AND a.name = 'Kích cỡ'
        ";
        $stmtSizes = $pdo->prepare($sqlSizes);
        $stmtSizes->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmtSizes->execute();
        $result['sizes'] = $stmtSizes->fetchAll(PDO::FETCH_ASSOC);


        // ---- 4. Lấy thông tin chi tiết của TẤT CẢ các biến thể ----
        // Dữ liệu này rất quan trọng để xử lý logic ở phía client (JavaScript)
        $sqlVariants = "
            SELECT
                pv.id AS variant_id,
                pv.stock,
                pv.price AS variant_price,
                GROUP_CONCAT(vv.attribute_value_id ORDER BY a.id) AS attribute_ids
            FROM product_variants pv
            JOIN variant_values vv ON pv.id = vv.variant_id
            JOIN attribute_values av ON vv.attribute_value_id = av.id
            JOIN attributes a ON av.attribute_id = a.id
            WHERE pv.product_id = :productId
            GROUP BY pv.id
        ";
        $stmtVariants = $pdo->prepare($sqlVariants);
        $stmtVariants->bindParam(':productId', $productId, PDO::PARAM_INT);
        $stmtVariants->execute();
        $result['variants'] = $stmtVariants->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ghi lại lỗi hoặc xử lý lỗi một cách phù hợp
        error_log("Database Error: " . $e->getMessage());
        return null;
    }

    return $result;
}

function getProductById(PDO $pdo, int $product_id)
{
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFeaturedProducts(PDO $pdo, int $limit = 4): array
{
    $sql = "SELECT
                p.id,
                p.name,
                p.price,
                p.description,
                pi.image_url AS main_image
            FROM
                products AS p
            LEFT JOIN
                product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
            WHERE
                p.is_featured = 1
                AND p.is_active = 1
            ORDER BY
                p.updated_at DESC
            LIMIT ?";

    try {
        $stmt = $pdo->prepare($sql);

        // Bind giá trị $limit vào placeholder
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi khi lấy sản phẩm nổi bật: " . $e->getMessage());
        return [];
    }
}

function get_product_images(PDO $pdo, int $product_id): array
{
    // Giả sử bạn có bảng product_images để lưu nhiều ảnh
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY created_at ASC");
    $stmt->execute([$product_id]);

    // fetchAll với PDO::FETCH_COLUMN sẽ trả về một mảng chỉ chứa giá trị của cột đó
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getProductByCategory(PDO $pdo, string $categoryId, int $limit = 12, int $offset = 0)
{
    $sql = "SELECT 
                p.*, 
                pi.image_url AS main_image
            FROM 
                products AS p
            LEFT JOIN 
                product_images AS pi ON p.id = pi.product_id AND pi.is_main = TRUE
            WHERE 
                p.is_active = TRUE
                AND p.category_id = :categoryId
            ORDER BY 
                p.created_at DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
