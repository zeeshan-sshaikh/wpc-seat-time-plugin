# XAMPP on macOS Troubleshooting

## Common Issues & Solutions

### 1. Apache Will Not Start
*   **Symptom:** "Apache Web Server" status remains "Stopped" in Manager-OSX.
*   **Cause 1: Port 80 Conflict.** macOS often runs a built-in Apache or other services on port 80.
    *   **Fix:** Run `sudo lsof -i :80` to identify the process.
    *   **Fix:** Stop the conflicting service (e.g., `sudo apachectl stop`).
    *   **Workaround:** Change XAMPP Apache port to 8080 in `/Applications/XAMPP/xamppfiles/etc/httpd.conf` (`Listen 8080`).
*   **Cause 2: Permissions.**
    *   **Fix:** Ensure ownership of `xamppfiles`. Run:
        ```bash
        sudo chown -R root:admin /Applications/XAMPP/xamppfiles
        sudo chmod -R 755 /Applications/XAMPP/xamppfiles
        ```

### 2. MySQL Will Not Start
*   **Symptom:** "MySQL Database" stops immediately after starting.
*   **Cause:** Corrupt data or PID file issues.
*   **Fix:** Check the MySQL error log at `/Applications/XAMPP/xamppfiles/var/mysql/[hostname].err`.
*   **Quick Fix (Force Recovery):**
    1.  Stop all XAMPP services.
    2.  Rename `mysql/data/ib_logfile0` and `ib_logfile1`.
    3.  Restart MySQL (it will recreate them).

### 3. File Permission Issues (WordPress Updates/Uploads)
*   **Symptom:** WordPress asks for FTP credentials or cannot upload images.
*   **Fix:** The web server (usually running as `daemon`) needs write access.
    ```bash
    sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/wordpress
    sudo chmod -R 775 /Applications/XAMPP/xamppfiles/htdocs/wordpress
    ```

## Key Configuration Files
*   **Apache:** `/Applications/XAMPP/xamppfiles/etc/httpd.conf`
*   **PHP:** `/Applications/XAMPP/xamppfiles/etc/php.ini`
*   **MySQL:** `/Applications/XAMPP/xamppfiles/etc/my.cnf`
