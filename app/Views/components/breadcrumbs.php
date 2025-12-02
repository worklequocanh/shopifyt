<?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
<nav class="bg-gray-50 py-3 border-b border-gray-200">
    <div class="container mx-auto px-4 lg:px-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <?php
            // Lấy item cuối cùng để so sánh
            $last_item = end($breadcrumbs);
            ?>

            <?php foreach ($breadcrumbs as $breadcrumb): ?>
                <li>
                    <?php if ($breadcrumb !== $last_item): ?>
                        <a href="<?php echo htmlspecialchars($breadcrumb['url']); ?>" class="hover:text-blue-600 hover:underline">
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </a>
                    <?php else: ?>
                        <span class="font-medium text-gray-700">
                            <?php echo htmlspecialchars($breadcrumb['title']); ?>
                        </span>
                    <?php endif; ?>
                </li>
                
                <?php if ($breadcrumb !== $last_item): ?>
                    <li class="text-gray-400">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
<?php endif; ?>