<?php
function getAllCategory(PDO $pdo)
{
  $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY created_at DESC");
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}