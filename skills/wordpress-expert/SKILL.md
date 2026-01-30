---
name: wordpress-expert
description: Specialized assistance for WordPress development, plugin/theme customization, performance optimization, and local environment troubleshooting (specifically XAMPP on macOS). Use when working on WordPress projects or encountering local server issues.
---

# WordPress Expert

## Overview

This skill transforms the agent into a WordPress specialist capable of handling core development tasks, debugging complex issues, optimizing performance, and resolving environment-specific problems with XAMPP on macOS.

## Workflow Decision Tree

1.  **Environment Issues?** (e.g., "Apache won't start", "Database connection error")
    *   See [XAMPP Troubleshooting](references/xampp_troubleshooting.md)

2.  **Development & Customization?**
    *   **New Plugin:** Use standard boilerplate structure.
    *   **Theme Customization:** Always use/check for a Child Theme first.
    *   **Debugging:** Enable `WP_DEBUG` (see `assets/wp-debug-config.php`).

3.  **Optimization?**
    *   See [Optimization Checklist](references/optimization_checklist.md)

## Core Capabilities

### 1. Local Environment (XAMPP/macOS)
*   **Common Issues:** Port 80 conflicts, MySQL socket errors, Permission denied.
*   **Paths:**
    *   `htdocs`: `/Applications/XAMPP/xamppfiles/htdocs`
    *   `php.ini`: `/Applications/XAMPP/xamppfiles/etc/php.ini`
    *   `httpd.conf`: `/Applications/XAMPP/xamppfiles/etc/httpd.conf`
    *   `my.cnf`: `/Applications/XAMPP/xamppfiles/etc/my.cnf`

### 2. Plugin Development
*   **Conventions:** Adhere to WordPress Coding Standards (WPCS).
*   **Security:** Always sanitize inputs (`sanitize_text_field`, `absint`) and escape outputs (`esc_html`, `esc_attr`).
*   **Hooks:** Use `add_action` and `add_filter` effectively; avoid modifying core files.

### 3. Theme Development
*   **Hierarchy:** Respect the Template Hierarchy (e.g., `single.php` > `index.php`).
*   **Child Themes:** Always recommend child themes for modifications to existing themes.
*   **Assets:** Use `wp_enqueue_script` and `wp_enqueue_style`.

## Quick Actions

### Enable Debugging
To quickly enable debugging, replace the debug section in `wp-config.php` with the content from `assets/wp-debug-config.php`.

### Database Connection Check
If "Error establishing a database connection":
1. Check `wp-config.php` credentials.
2. Verify MySQL is running in XAMPP Manager.
3. Check if port 3306 is blocked.