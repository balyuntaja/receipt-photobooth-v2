# Production Deployment

## Upload limit (413 Request Entity Too Large)

Foto booth bisa menghasilkan file besar (khususnya photostrip/merged image). Pastikan limit upload cukup:

### PHP (`php.ini` atau `.user.ini` di `public/`)

```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### Nginx

```nginx
client_max_body_size 25M;
```

Tambahkan di dalam block `server { }` Anda.
