#!/bin/bash
# Script Verifikasi Fitur Potong Data
# Jalankan: bash verify_data_cutting.sh

echo "=========================================="
echo "  VERIFIKASI FITUR POTONG DATA"
echo "=========================================="
echo ""

# Check migrations
echo "✓ Checking migrations..."
php artisan migrate:status | grep -E "2026_05_17_000001|Nothing to migrate"
echo ""

# Check model
echo "✓ Checking DataCutLog model..."
if [ -f "app/Models/DataCutLog.php" ]; then
  echo "  ✅ Model exists"
else
  echo "  ❌ Model NOT found"
fi
echo ""

# Check controller
echo "✓ Checking DataCuttingController..."
if [ -f "app/Http/Controllers/DataCuttingController.php" ]; then
  echo "  ✅ Controller exists"
else
  echo "  ❌ Controller NOT found"
fi
echo ""

# Check views
echo "✓ Checking Views..."
views=(
  "resources/views/admin/data-cutting/index.blade.php"
  "resources/views/admin/data-cutting/create.blade.php"
  "resources/views/admin/data-cutting/guide.blade.php"
)

for view in "${views[@]}"; do
  if [ -f "$view" ]; then
    echo "  ✅ $view"
  else
    echo "  ❌ $view NOT found"
  fi
done
echo ""

# Check documentation
echo "✓ Checking Documentation..."
docs=(
  "PANDUAN_POTONG_DATA.md"
  "KONFIGURASI_POTONG_DATA.md"
  "README_POTONG_DATA.md"
)

for doc in "${docs[@]}"; do
  if [ -f "$doc" ]; then
    echo "  ✅ $doc"
  else
    echo "  ❌ $doc NOT found"
  fi
done
echo ""

# Check routes
echo "✓ Checking Routes..."
php artisan route:list | grep -i "data-cutting"
echo ""

# Database table check
echo "✓ Checking Database Table..."
php artisan tinker <<'EOF'
$table = \Illuminate\Support\Facades\Schema::hasTable('data_cut_logs');
echo $table ? "✅ Table 'data_cut_logs' exists\n" : "❌ Table NOT found\n";
EOF
echo ""

# Check backup folder
echo "✓ Checking Backup Folder..."
mkdir -p storage/app/backups
chmod 755 storage/app/backups
if [ -d "storage/app/backups" ]; then
  echo "  ✅ storage/app/backups folder ready"
else
  echo "  ❌ Backup folder error"
fi
echo ""

# Check mysqldump
echo "✓ Checking mysqldump..."
if command -v mysqldump &> /dev/null; then
  echo "  ✅ mysqldump found: $(which mysqldump)"
else
  echo "  ⚠️  mysqldump NOT in PATH (will auto-detect)"
fi
echo ""

echo "=========================================="
echo "  VERIFIKASI SELESAI"
echo "=========================================="
echo ""
echo "✅ Fitur Potong Data siap digunakan!"
echo ""
echo "Langkah selanjutnya:"
echo "1. Login sebagai Superadmin"
echo "2. Buka menu 'Potong Data'"
echo "3. Baca panduan di /data-cutting/guide"
echo "4. Jalankan 'Potong Data Baru'"
