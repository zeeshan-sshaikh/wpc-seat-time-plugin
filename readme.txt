=== WPC Seat Time Estimates for LearnDash ===
Contributors: WPC
Tags: learndash, seat time, estimate, course duration, time to complete
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automated calculation and display of estimated seat time based on word count, media duration, and H5P content for LearnDash courses.

== Description ==

This plugin automatically calculates and displays the estimated "Seat Time" (time to complete) for LearnDash courses, lessons, topics, and quizzes.

It uses a smart cumulative logic to sum up:
1.  **Reading Time**: Calculated from word count using configurable reading speeds (WPM).
2.  **Video Duration**: Automatically fetched from YouTube and Vimeo APIs.
3.  **Audio/Video Files**: Detects duration of local MP3/MP4 files.
4.  **H5P Content**: Detects H5P activities and applies a default estimated time.
5.  **Manual Overrides**: Instructors can manually set or adjust time for any specific content.

The plugin displays these estimates on the frontend, helping students understand the time commitment required for each section of the course.

== Installation ==

1.  Upload the plugin folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to **Settings > Seat Time Estimates** to configure your preferred reading speeds and API keys.
4.  Visit any LearnDash course, lesson, or topic to see the estimates in action.

== Frequently Asked Questions ==

= How is reading time calculated? =
It is based on the total word count divided by the "Average Reading Speed" (default 200 WPM) set in the options.

= Can I change the reading speed? =
Yes, you can adjust the WPM in the settings page.

= Does it support video? =
Yes, it supports YouTube, Vimeo, and local media files. You may need to provide a YouTube Data API key in the settings for YouTube support.

== Changelog ==

= 1.0.0 =
*   Initial release with cumulative time calculation, media support, and H5P integration.
