<?php
require_once __DIR__ . '/includes/functions/auth_functions.php';
require_once __DIR__ . '/includes/functions/functions.php';
require_once __DIR__ . '/includes/functions/product_functions.php';

$page_title = 'Về chúng tôi';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <?php include __DIR__ . '/includes/layouts/head.php'; ?>
</head>

<body class="bg-gray-50 text-gray-800">
  <?php include __DIR__ . '/includes/layouts/header.php'; ?>

  <main>
    <section class="relative h-[400px] lg:h-[500px] flex items-center justify-center text-center bg-cover bg-center bg-fixed"
      style="background-image: url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=2074&auto=format&fit=crop');">
      <div class="absolute inset-0 bg-black/50"></div>
      <div class="relative z-10 container mx-auto px-4">
        <h1 class="text-4xl lg:text-6xl font-extrabold text-white mb-4 tracking-tight">
          Hơn Cả Thời Trang. <br> Đó Là Phong Cách Sống.
        </h1>
        <p class="text-lg lg:text-xl text-gray-200 max-w-2xl mx-auto">
          Tại STYLEX, chúng tôi tin rằng trang phục bạn mặc là lời chào không lời gửi đến thế giới.
        </p>
      </div>
    </section>

    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="order-2 lg:order-1">
            <div class="grid grid-cols-2 gap-4">
              <img src="https://images.unsplash.com/photo-1529139574466-a3005241bb5a?q=80&w=1887&auto=format&fit=crop" class="rounded-lg shadow-lg transform translate-y-8">
              <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop" class="rounded-lg shadow-lg">
            </div>
          </div>
          <div class="order-1 lg:order-2">
            <h2 class="text-blue-600 font-bold uppercase tracking-wide text-sm mb-2">Câu chuyện của chúng tôi</h2>
            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">Khởi nguồn từ niềm đam mê sự tối giản</h3>
            <div class="prose text-gray-600 leading-relaxed space-y-4">
              <p>
                Được thành lập vào năm 2025, STYLEX bắt đầu với một ý tưởng đơn giản: Thời trang cao cấp không nhất thiết phải đắt đỏ, và sự đơn giản chính là đỉnh cao của sự tinh tế.
              </p>
              <p>
                Chúng tôi không chạy theo những xu hướng nhất thời (fast fashion). Thay vào đó, chúng tôi tập trung vào những thiết kế bền vững, chất liệu thân thiện và phom dáng chuẩn mực, giúp bạn tự tin trong mọi hoàn cảnh.
              </p>
              <p>
                Mỗi sản phẩm của STYLEX đều trải qua quy trình kiểm tra nghiêm ngặt, từ khâu chọn vải đến từng đường kim mũi chỉ, đảm bảo mang đến trải nghiệm tốt nhất cho khách hàng.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-gray-50 py-16 lg:py-24">
      <div class="container mx-auto px-4 lg:px-6 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-12">Giá Trị Cốt Lõi</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-3">Chất Lượng Hàng Đầu</h3>
            <p class="text-gray-600">Cam kết sử dụng chất liệu tốt nhất, bền bỉ theo thời gian và an toàn cho làn da.</p>
          </div>
          <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="w-16 h-16 mx-auto bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex justify-center items-center gap-2 mb-3">
              <h3 class="text-xl font-bold">Thời Trang Bền Vững</h3>
            </div>
            <p class="text-gray-600">Quy trình sản xuất giảm thiểu tác động đến môi trường và hướng tới tương lai xanh.</p>
          </div>
          <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="w-16 h-16 mx-auto bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold mb-3">Khách Hàng Là Bạn</h3>
            <p class="text-gray-600">Dịch vụ chăm sóc khách hàng tận tâm, hỗ trợ đổi trả linh hoạt và tư vấn 24/7.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-900 text-white">
      <div class="container mx-auto px-4 lg:px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-4xl lg:text-5xl font-bold text-blue-400 mb-2">5+</div>
            <div class="text-gray-400">Năm hoạt động</div>
          </div>
          <div>
            <div class="text-4xl lg:text-5xl font-bold text-blue-400 mb-2">10k+</div>
            <div class="text-gray-400">Khách hàng hài lòng</div>
          </div>
          <div>
            <div class="text-4xl lg:text-5xl font-bold text-blue-400 mb-2">500+</div>
            <div class="text-gray-400">Mẫu thiết kế</div>
          </div>
          <div>
            <div class="text-4xl lg:text-5xl font-bold text-blue-400 mb-2">24h</div>
            <div class="text-gray-400">Giao hàng nhanh</div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4 lg:px-6 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-12">Gặp Gỡ Đội Ngũ Sáng Tạo</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="group">
            <div class="relative overflow-hidden rounded-lg mb-4">
              <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=1887&auto=format&fit=crop" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
            <h3 class="text-xl font-bold">Nguyễn Văn A</h3>
            <p class="text-blue-600">Founder & CEO</p>
          </div>
          <div class="group">
            <div class="relative overflow-hidden rounded-lg mb-4">
              <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=1888&auto=format&fit=crop" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
            <h3 class="text-xl font-bold">Trần Thị B</h3>
            <p class="text-blue-600">Giám đốc sáng tạo</p>
          </div>
          <div class="group">
            <div class="relative overflow-hidden rounded-lg mb-4">
              <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=1887&auto=format&fit=crop" class="w-full h-80 object-cover transition-transform duration-500 group-hover:scale-105">
            </div>
            <h3 class="text-xl font-bold">Lê Văn C</h3>
            <p class="text-blue-600">Trưởng phòng Marketing</p>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-blue-50 py-16 lg:py-20">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Bạn đã sẵn sàng thay đổi phong cách?</h2>
        <p class="text-gray-600 mb-8 max-w-2xl mx-auto">Khám phá bộ sưu tập mới nhất của chúng tôi và tìm ra những món đồ thể hiện cá tính của bạn ngay hôm nay.</p>
        <a href="products.php" class="inline-block bg-gray-900 text-white font-bold py-3 px-8 rounded-lg hover:bg-gray-800 transition-transform hover:-translate-y-1 shadow-lg">
          Xem Sản Phẩm Ngay
        </a>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/includes/layouts/footer.php'; ?>

</body>

</html>