<?php
$orderHistory = getOrderHistoryForAccount($pdo);
?>

<div class="bg-white p-8 rounded-lg shadow-md">
  <h2 class="text-2xl font-bold mb-6 text-gray-900">Lịch sử đơn hàng</h2>

  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead class="border-b bg-gray-50">
        <tr>
          <th class="p-4 font-semibold">Mã đơn hàng</th>
          <th class="p-4 font-semibold">Ngày đặt</th>
          <th class="p-4 font-semibold">Tổng tiền</th>
          <th class="p-4 font-semibold">Trạng thái</th>
          <th class="p-4 font-semibold"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orderHistory as $order): ?>
        <tr class="border-b">
          <td class="p-4"><?php echo htmlspecialchars($order['order_code']); ?></td>
          <td class="p-4 text-gray-600"><?php echo htmlspecialchars($order['order_date']); ?></td>
          <td class="p-4 font-medium"><?php echo htmlspecialchars($order['total_amount']); ?></td>
          <td class="p-4">
            <span class="px-3 py-1 text-xs font-medium rounded-full
              <?php if ($order['status'] === 'Hoàn thành'): ?>bg-green-100 text-green-800
              <?php elseif ($order['status'] === 'Đang xử lý'): ?>bg-yellow-100 text-yellow-800
              <?php else: ?>bg-red-100 text-red-800
              <?php endif; ?>
            ">
              <?php echo htmlspecialchars($order['status']); ?>
            </span>
          </td>
          <td class="p-4 text-right">
            <a href="#" class="font-medium text-blue-600 hover:underline">Xem chi tiết</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <!-- <tr class="border-b">
          <td class="p-4">#123455</td>
          <td class="p-4 text-gray-600">15/10/2025</td>
          <td class="p-4 font-medium">1.500.000đ</td>
          <td class="p-4">
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
              Đang xử lý
            </span>
          </td>
          <td class="p-4 text-right">
            <a href="#" class="font-medium text-blue-600 hover:underline">Xem chi tiết</a>
          </td>
        </tr>
        <tr class="border-b">
          <td class="p-4">#123450</td>
          <td class="p-4 text-gray-600">01/10/2025</td>
          <td class="p-4 font-medium">250.000đ</td>
          <td class="p-4">
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
              Đã hủy
            </span>
          </td>
          <td class="p-4 text-right">
            <a href="#" class="font-medium text-blue-600 hover:underline">Xem chi tiết</a>
          </td>
        </tr> -->
      </tbody>
    </table>
  </div>

</div>