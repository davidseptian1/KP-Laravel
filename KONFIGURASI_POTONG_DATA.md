# Konfigurasi Fitur Potong Data (Data Cutting)

## Persyaratan Sistem

### MySQL/MariaDB
- Harus terinstall dan berjalan
- Minimum version: 5.7+
- Tools: mysqldump (biasanya included)

### Path MySQL (Windows)
Supported paths:
- `C:\xampp\mysql\bin` (XAMPP)
- `C:\laragon\bin\mysql\mysql-5.7.26-win32-x64\bin` (Laragon)
- `C:\laragon\bin\mysql\mysql-5.7.36-winx64\bin` (Laragon)
- `C:\laragon\bin\mysql\mysql-8.0.1-winx64\bin` (Laragon)
- `C:\Program Files\MySQL\MySQL Server 8.0\bin` (Direct install)

Jika sudah di PATH environment, sistem otomatis mendeteksi.

## Database Configuration

Pastikan `.env` memiliki konfigurasi database yang benar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_tilangan
DB_USERNAME=root
DB_PASSWORD=
```

## Backup Storage

Folder backup:
- Default: `storage/app/backups/`
- Pastikan folder writable (chmod 755)
- Disarankan: Cleanup backup lama secara berkala

Cara cleanup:
```php
// Di storage/app/backups/
// Hapus file backup_*.sql yang sudah lama (>3 bulan)
```

## Migration Status

Check migration history:
```bash
php artisan migrate:status
```

Expected output:
```
2026_05_17_000001_create_data_cut_logs_table .......................... YES
```

## Role & Permission

Fitur ini hanya untuk:
- **Superadmin** - Full access

Data di tabel `users` harus memiliki:
- Column: `jabatan`
- Value: `'superadmin'` (case-sensitive, lowercase)

## Routes Information

```
GET  /data-cutting              → Riwayat potong data
GET  /data-cutting/guide        → Panduan lengkap
GET  /data-cutting/create       → Form create baru
POST /data-cutting/preview      → Preview AJAX
POST /data-cutting              → Process backup & delete
GET  /data-cutting/{id}/download → Download backup file
```

## File Locations

### Controllers
- `app/Http/Controllers/DataCuttingController.php`

### Models
- `app/Models/DataCutLog.php`

### Views
- `resources/views/admin/data-cutting/index.blade.php`
- `resources/views/admin/data-cutting/create.blade.php`
- `resources/views/admin/data-cutting/guide.blade.php`

### Database
- `database/migrations/2026_05_17_000001_create_data_cut_logs_table.php`

### Documentation
- `PANDUAN_POTONG_DATA.md`

## Testing Checklist

- [ ] Can access menu "Potong Data" (Superadmin only)
- [ ] Can see riwayat page with empty state
- [ ] Can click "Potong Data Baru" button
- [ ] Form loads with default date (2 months ago)
- [ ] Preview loads AJAX data correctly
- [ ] Can select different dates (preview updates)
- [ ] Can tick backup checkbox
- [ ] Can enter notes
- [ ] Can tick konfirmasi checkbox
- [ ] Submit button enables after konfirmasi
- [ ] Process runs (check status changes)
- [ ] Backup file created (check storage/app/backups/)
- [ ] Data count matches deleted records
- [ ] Can view detail with modal
- [ ] Can download backup file
- [ ] Riwayat shows completed status
- [ ] Error handling works (if mysqldump missing)

## Troubleshooting Commands

Check mysqldump location:
```bash
where mysqldump                 # Windows
which mysqldump                 # Linux/Mac
```

Test MySQL connection:
```bash
mysql -u root -h 127.0.0.1 -D laravel_tilangan
```

Check database size:
```sql
SELECT 
    table_name, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES 
WHERE table_schema = 'laravel_tilangan'
ORDER BY size_mb DESC;
```

Check backup folder permissions:
```bash
ls -la storage/app/backups/     # Linux/Mac
icacls storage/app/backups      # Windows
```

## Performance Considerations

### Database Indexes
Data Cut Log table indexes:
- `user_id`
- `created_at`

Add more indexes if needed for faster queries:
```sql
ALTER TABLE data_cut_logs ADD INDEX idx_status (status);
ALTER TABLE data_cut_logs ADD INDEX idx_cut_date (cut_date);
```

### Backup Size Estimation
Backup file size ≈ Database size (slightly larger due to SQL format)

Example:
- 100 MB database → ~110 MB backup file
- 1 GB database → ~1.1 GB backup file

Make sure disk has 2x database size available.

## Security Notes

1. Only Superadmin can access (authorization check in controller)
2. CSRF protection enabled (form includes csrf_token)
3. No data exported outside system (backup only SQL format)
4. Error logs sanitized (no sensitive data exposure)
5. User activity logged (via AdminActivityLog if enabled)

## Maintenance

### Regular Tasks
- **Weekly**: Check backup folder size
- **Monthly**: Cleanup old backups (>3 months)
- **Quarterly**: Review deletion logs for audit
- **Yearly**: Archive old backups to external storage

### Monitoring
- Check `data_cut_logs` table size growth
- Monitor `storage/app/backups/` folder
- Review error logs if process fails

## Restore Procedure

If need to restore from backup:

1. Download backup file from UI
2. SSH/RDP ke server
3. Run:
   ```bash
   mysql -u root -p laravel_tilangan < backup_YYYY-MM-DD_HHMMSS.sql
   ```
4. Verify data restored correctly:
   ```bash
   php artisan tinker
   >>> App\Models\Transaksi::count()
   ```

## Known Limitations

1. Cannot backup database > 2 GB (might timeout)
   - Solution: Use manual mysqldump or split backup
2. No real-time progress bar (system cannot track progress)
   - Solution: Check browser console for update messages
3. No automatic retry on failure
   - Solution: Manual retry required

## Future Enhancements

Possible improvements:
- [ ] Scheduled auto-backup command
- [ ] Email notification after process
- [ ] Database size dashboard
- [ ] Retention policy settings
- [ ] Incremental backup support
- [ ] Progress webhook/SSE
- [ ] Backup compression (gzip)
- [ ] Multi-database support
