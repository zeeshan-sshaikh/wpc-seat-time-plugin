WordPress Plugin: WPC Seat Time Estimates for LearnDash

Summary:
This plugin calculates and displays estimated seat time for LearnDash courses.

Requirements:
- WordPress installation.
- LearnDash must be installed and active.

Functionality:
1. The plugin fetches all lessons, topics, and quizzes within a LearnDash course.
2. It extracts plain text content only, stripping HTML and ignoring images, videos, and other media.
3. Total word count is calculated.
4. Estimated seat time is calculated using a simple formula (version 1):
   Estimated time = Total words ÷ Reading speed

Example:
- Average reading speed: 200 words/min
- Slow reading speed: 120 words/min

Output:
- The plugin displays the estimated seat time directly on all relevant LearnDash pages, including course, lessons, topics, and quizzes.

Notes:
- This is a basic version. Future versions may include media time and user behavior tracking for more accurate estimates.
- The formula provides an estimated range (e.g., 15–25 minutes) for course completion.