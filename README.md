# WPC Seat Time Estimates for LearnDash

A WordPress plugin that automatically calculates and displays estimated seat time for LearnDash courses, lessons, topics, and quizzes. This provides students with a clear and accurate time commitment, helping them better plan their learning schedule.

## Features

*   **Automated Time Calculation:** Calculates word counts for all LearnDash content and converts them into "Seat Time" (in minutes) based on adjustable reading speeds.
*   **Media & Interactive Support:**
    *   **YouTube:** Automatically retrieves video duration via YouTube Data API v3.
    *   **Vimeo:** Fetches duration automatically via oEmbed.
    *   **Local Media:** Uses getID3 to parse metadata from self-hosted MP3/MP4 files.
    *   **H5P:** Detects H5P shortcodes and applies configurable completion estimates.
*   **Smart Cumulative Display:** The time displayed for a course, lesson, or topic is a cumulative total of its own content plus all nested content.
*   **Performance Optimized:** Calculations occur on save/update using WordPress Transients for caching.

## Prerequisites

*   WordPress `5.0` or higher.
*   PHP `7.2` or higher.
*   [LearnDash](https://www.learndash.com/) plugin installed and activated.

---

## Quick Start

### Development Setup
1.  **Environment**: Ensure you have a WordPress environment with LearnDash 4.0+ installed.
2.  **Plugin Activation**: Activate the "WPC Seat Time Estimates for LearnDash" plugin.
3.  **Settings**:
    *   Navigate to **LearnDash LMS > Settings > Seat Time Estimates**.
    *   Configure your "Average Reading Speed" and "Slow Reading Speed".
    *   Set a "Default H5P Time" (optional).
    *   Add a YouTube API key for reliable duration detection (optional).

### Feature Usage
*   **Automatic Estimation**: Create or edit a LearnDash Lesson, Topic, or Quiz. Add text, embed videos, or H5P shortcodes and save. The plugin calculates the estimate automatically.
*   **Manual Overrides**: Use the **Seat Time Settings** meta box on the Lesson/Topic edit page to manually set duration in seconds.

---

## Installation

1.  Download the plugin as a `.zip` file from the [Releases](https://github.com/zeeshan-sshaikh/wpc-seat-time-plugin/releases) page.
2.  In your WordPress admin dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `.zip` file.
4.  Click **Install Now**, and then **Activate Plugin**.
5.  Navigate to **Settings > Seat Time Estimates** to configure options.

---

## Development Environment Setup

Instructions for developers who want to contribute.

### 1. Clone the Repository
```bash
git clone https://github.com/zeeshan-sshaikh/wpc-seat-time-plugin.git
cd wpc-seat-time-plugin
```

### 2. Local Environment
Place the cloned directory inside your local WordPress `wp-content/plugins/` folder and activate it.

### 3. Dependencies
Self-contained; requires no `composer` or `npm` dependencies for core functionality.
