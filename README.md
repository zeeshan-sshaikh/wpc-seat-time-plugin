# WPC Seat Time Estimates for LearnDash

A WordPress plugin that automatically calculates and displays estimated seat time for LearnDash courses, lessons,topics, and quizzes. This provides students with a clear and accurate time commitment, helping them better plan their learning schedule.

## Features

*   **Automated Time Calculation:** Calculates word counts for all LearnDash content and converts them into "Seat Time" (in minutes) based on adjustable reading speeds.
*   **Smart Cumulative Display:** The time displayed for a course, lesson, or topic is a cumulative total of its own content plus all nested content (e.g., a lesson's time includes all its topics and quizzes). This gives students a true "total effort" estimate.
*   **Intelligent Hierarchy Updates:** When content is updated (e.g., a topic is changed), the plugin automatically "bubbles up" the changes to recalculate the time for the parent lesson and course.
*   **Admin Configuration Panel:** A dedicated settings page at **Settings > Seat Time Estimates** allows administrators to:
    *   Set custom "Average" and "Slow" reading speeds (WPM) to match their audience.
    *   Customize the display label (e.g., "Estimated Seat Time:", "Time to Complete:").
*   **Seamless Frontend Integration:** Natively integrates with LearnDash templates and includes a fallback display filter to ensure estimates appear correctly even on highly customized themes.
*   **Performance Optimized:** Calculations are performed when content is saved, not on every page load, ensuring your site remains fast.

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

**For macOS:**

1.  Ensure you have a local server environment (e.g., [MAMP](https://www.mamp.info/en/mamp/mac/)) installed.
2.  Follow the steps in section 2 to place the plugin in your WordPress directory.
3.  Ensure Git is installed. If not, open Terminal and run `xcode-select --install`.

**For Windows:**

1.  Ensure you have a local server environment (e.g., [XAMPP](https://www.apachefriends.org/index.html)) installed.
2.  Follow the steps in section 2 to place the plugin in your WordPress directory.
3.  Ensure Git is installed. You can download it from [git-scm.com](https://git-scm.com/download/win).
