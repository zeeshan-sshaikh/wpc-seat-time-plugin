# WPC Seat Time Estimates for LearnDash

A WordPress plugin that automatically calculates and displays estimated seat time for LearnDash courses, lessons, topics, and quizzes. This provides students with a clear and accurate time commitment, helping them better plan their learning schedule.

## Features

*   **Automated Time Calculation:** Calculates word counts for all LearnDash content and converts them into "Seat Time" (in minutes) based on adjustable reading speeds.
*   **Media & Interactive Support:**
    *   **YouTube:** Automatically retrieves video duration via YouTube Data API v3.
    *   **Vimeo:** Fetches duration automatically via oEmbed.
    *   **Local Media:** Uses getID3 to parse metadata from self-hosted MP3/MP4 files.
    *   **H5P:** Detects H5P shortcodes and applies configurable completion estimates.
*   **Smart Cumulative Display:** The time displayed for a course, lesson, or topic is a cumulative total of its own content plus all nested content (e.g., a lesson's time includes all its topics and quizzes).
*   **Intelligent Hierarchy Updates:** When content is updated (e.g., a topic is changed), the plugin automatically "bubbles up" the changes to recalculate the parent lesson and course.
*   **Admin Configuration Panel:** Dedicated settings at **Settings > Seat Time Estimates** to configure WPM, YouTube API Keys, and H5P defaults.
*   **Performance Optimized:** Calculations occur on save/update, utilizing WordPress Transients for API caching to ensure zero impact on frontend speed.

## Prerequisites

*   WordPress `5.0` or higher.
*   PHP `7.2` or higher.
*   [LearnDash](https://www.learndash.com/) plugin installed and activated.

## Quick Start & Installation

1.  Download the plugin as a `.zip` file from the [Releases](https://github.com/zeeshan-sshaikh/wpc-seat-time-plugin/releases) page.
2.  In your WordPress admin dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `.zip` file you downloaded.
4.  Click **Install Now**, and then **Activate Plugin**.
5.  Navigate to **Settings > Seat Time Estimates** to configure the reading speed and other options.

---

## Development Environment Setup

Instructions for developers who want to contribute to the plugin.

### 1. Clone the Repository

```bash
git clone https://github.com/zeeshan-sshaikh/wpc-seat-time-plugin.git
cd wpc-seat-time-plugin
```

### 2. Local Environment

This plugin is designed to run in a standard WordPress environment. A local development server like XAMPP, MAMP, or Local is recommended.

*   Place the cloned `wpc-seat-time-plugin` directory inside your local WordPress installation's `wp-content/plugins/` folder.
*   Activate the plugin from the WordPress admin dashboard.

### 3. Dependencies

This plugin is self-contained and does not require `composer` or `npm` dependencies for its core functionality. All you need is a running WordPress installation with the LearnDash plugin.
