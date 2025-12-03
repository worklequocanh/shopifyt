<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Quản lý</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
  .product-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .report-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border-left: 4px solid;
  }

  .report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  }

  .stat-card {
    border-left: 4px solid;
    min-height: 120px;
  }

  .stat-number {
    font-size: 2rem;
    font-weight: bold;
  }

  .sidebar {
    min-height: calc(100vh - 80px);
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
  }

  .sidebar .nav-link {
    color: #495057;
    padding: 0.75rem 1rem;
    margin: 0.25rem 0;
    border-radius: 0.375rem;
  }

  .sidebar .nav-link:hover {
    background: #e9ecef;
  }

  .sidebar .nav-link.active {
    background: #0d6efd;
    color: white;
  }

  .sidebar .nav-link i {
    margin-right: 0.5rem;
  }
</style>
