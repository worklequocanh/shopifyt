<nav class="mt-12">
  <?php if ($total_pages > 1): ?>
  <ul class="flex items-center justify-center space-x-2">
    <?php if ($current_page > 1): ?>
    <li>
      <a href="products.php?page=<?php echo $current_page - 1; ?>"
        class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100">
        &laquo; Trước
      </a>
    </li>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <li>
      <a href="products.php?page=<?php echo $i; ?>" class="px-4 py-2 border rounded-lg 
                          <?php echo ($i == $current_page)
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white hover:bg-gray-100'; ?>">
        <?php echo $i; ?>
      </a>
    </li>
    <?php endfor; ?>

    <?php if ($current_page < $total_pages): ?>
    <li>
      <a href="products.php?page=<?php echo $current_page + 1; ?>"
        class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-100">
        Sau &raquo;
      </a>
    </li>
    <?php endif; ?>
  </ul>
  <?php endif; ?>
</nav>