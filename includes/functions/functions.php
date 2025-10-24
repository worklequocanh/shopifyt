<?php

function format_currency(float $number): string
{
    return number_format($number, 0, ',', '.') . 'đ';
}

// 
function sanitize_input(string $data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect(string $url): void
{
    header("Location: /{$url}");
    exit();
}

function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function displayFlashMessage(): void
{
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $type = $flash['type'];
        $message = $flash['message'];

        $color_classes = [
            'success' => 'bg-green-100 border-green-400 text-green-700',
            'error'   => 'bg-red-100 border-red-400 text-red-700',
            'info'    => 'bg-blue-100 border-blue-400 text-blue-700'
        ];

        $class = $color_classes[$type] ?? $color_classes['info'];

        echo "<div class='border px-4 py-3 rounded-lg relative {$class}' role='alert'>";
        echo "<span class='block sm:inline'>{$message}</span>";
        echo "</div>";

        // Xóa thông báo sau khi hiển thị
        unset($_SESSION['flash_message']);
    }
}