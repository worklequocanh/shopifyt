<?php if (isset($success_message) && !empty($success_message)): ?>
<div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700 border border-green-200" role="alert">
  <div class="flex items-center">
    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
      stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <span class="font-medium">Thành công!</span>&nbsp;<?php echo htmlspecialchars($success_message); ?>
  </div>
</div>
<?php endif; ?>

<?php if (isset($error_message) && !empty($error_message)): ?>
<div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700 border border-red-200" role="alert">
  <div class="flex items-center">
    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
      stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    <span class="font-medium">Lỗi!</span>&nbsp;<?php echo htmlspecialchars($error_message); ?>
  </div>
</div>
<?php endif; ?>

<?php if (isset($warning_message) && !empty($warning_message)): ?>
<div class="mb-4 rounded-lg bg-yellow-100 p-4 text-sm text-yellow-700 border border-yellow-200" role="alert">
  <div class="flex items-center">
    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
      stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
    <span class="font-medium">Chú ý!</span>&nbsp;<?= htmlspecialchars($warning_message); ?>
  </div>
</div>
<?php endif; ?>