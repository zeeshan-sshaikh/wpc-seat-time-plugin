# WordPress Optimization Checklist

## 1. Database Optimization
- [ ] **Clean up Transients:** Remove expired transients using a plugin or CLI (`wp transient delete --expired`).
- [ ] **Limit Revisions:** Set `define( 'WP_POST_REVISIONS', 5 );` in `wp-config.php`.
- [ ] **Optimize Tables:** Run `OPTIMIZE TABLE` on bloated tables (check `wp_options`).

## 2. Asset Management
- [ ] **Minify CSS/JS:** Ensure assets are minified.
- [ ] **Defer/Async Scripts:** Move non-critical JS to footer or use `defer`.
- [ ] **Image Optimization:** Use WebP format and ensure proper sizing (`srcset`).

## 3. Code Performance
- [ ] **N+1 Queries:** Check loops for database queries. Use `wp_cache_get` or eager loading.
- [ ] **Autoloaded Options:** Check `wp_options` for large `autoload='yes'` rows.
- [ ] **Fragment Caching:** Cache expensive HTML blocks using Transients API.

## 4. XAMPP Local Speed
- [ ] **Disable XDebug:** If not actively debugging, disable XDebug in `php.ini` as it significantly slows down PHP execution.
    *   Comment out `zend_extension = xdebug.so`
