# WordPress Hooks and API Contracts for WPC Seat Time Estimates for LearnDash

This document outlines the key WordPress hooks, filters, and API interactions that the plugin will utilize to integrate with WordPress and LearnDash.

## 1. Content Processing and Calculation Triggers

### `save_post` Action Hook

*   **Description**: Triggers when a post is saved or updated. This hook will be used to recalculate and store the seat time estimates for LearnDash post types.
*   **Context**: Fires for all post types. Will need to check `post_type` to ensure it's a LearnDash Course, Lesson, Topic, or Quiz.
*   **Parameters**: `(int) $post_id`, `(WP_Post) $post`, `(bool) $update`
*   **Action**:
    *   Check if `$post->post_type` is a relevant LearnDash post type.
    *   Strip HTML and count words from `$post->post_content`.
    *   Fetch plugin settings (average/slow WPM).
    *   Calculate `estimated_minutes_average` and `estimated_minutes_slow`.
    *   Save these estimates as `post_meta` using `update_post_meta()`.

### Filters for Content Manipulation (Internal)

*   **Description**: Internal filters might be used to pre-process content before word counting, e.g., to exclude specific shortcode content if necessary. (NEEDS FURTHER RESEARCH DURING IMPLEMENTATION)

## 2. Frontend Display

### `learndash_course_before` / `learndash_lesson_before` / etc. Action Hooks (or similar)

*   **Description**: Hooks provided by LearnDash to insert content before the main course/lesson/topic/quiz content. These will be used to display the estimated seat time to students.
*   **Context**: Specific to LearnDash templates.
*   **Action**:
    *   Fetch `post_meta` for the current post's estimated seat time.
    *   Format the display string (e.g., "Estimated Seat Time: 15-25 minutes").
    *   Echo the formatted string.
*   **Alternative Consideration**: Using the `the_content` filter might also be an option, but LearnDash-specific hooks offer better control for placement within their templates.

## 3. Administration Settings

### `admin_menu` Action Hook

*   **Description**: Used to add a new top-level or sub-menu page in the WordPress admin area for plugin settings.
*   **Context**: Fires during the admin initialization.
*   **Action**:
    *   Call `add_options_page()` or `add_submenu_page()` to register the settings page.

### WordPress Settings API

*   **Description**: The recommended way to create and manage settings pages in WordPress.
*   **Context**: Used within the settings page callback.
*   **Action**:
    *   Register settings, sections, and fields using `register_setting()`, `add_settings_section()`, `add_settings_field()`.
    *   Handle sanitization and validation of input using callbacks.
    *   Store settings using `update_option()` and retrieve using `get_option()`.

## 4. LearnDash Integration

### LearnDash API / Helper Functions

*   **Description**: LearnDash provides its own set of functions and classes to interact with its custom post types and functionalities.
*   **Context**: Needed to identify LearnDash post types, check course progression, etc.
*   **Action**:
    *   Utilize functions like `learndash_get_post_type_slug()` to correctly identify course, lesson, topic, and quiz post types.
    *   Potentially use LearnDash hooks to ensure compatibility with their template system.

## 5. Plugin Activation/Deactivation/Uninstall

### `register_activation_hook()`

*   **Description**: Function to run code upon plugin activation.
*   **Action**: Initialize default settings if they don't exist.

### `register_deactivation_hook()`

*   **Description**: Function to run code upon plugin deactivation.
*   **Action**: Clean up temporary data if necessary (though generally not for deactivation).

### `register_uninstall_hook()`

*   **Description**: Function to run code when the plugin is uninstalled.
*   **Action**: Remove all plugin data (settings, post meta added by the plugin) from the database to ensure a clean uninstall.
