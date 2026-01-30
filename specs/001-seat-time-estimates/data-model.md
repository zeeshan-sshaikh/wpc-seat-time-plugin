# Data Model: WPC Seat Time Estimates for LearnDash

## Key Entities

### Seat Time Estimate

Represents the calculated duration for a specific LearnDash post (Course, Lesson, Topic, Quiz). This data will primarily be stored as `post_meta` for the respective LearnDash post types.

**Attributes**:

*   **`post_id`** (integer): The ID of the LearnDash post (Course, Lesson, Topic, Quiz). This serves as the primary key and links to `wp_posts.ID`.
*   **`word_count`** (integer): The total number of words in the post's content after stripping HTML and other non-text elements.
*   **`estimated_minutes_average`** (float): The estimated completion time in minutes based on the configured "Average Reading Speed".
*   **`estimated_minutes_slow`** (float): The estimated completion time in minutes based on the configured "Slow Reading Speed".

**Relationships**:

*   **`wp_posts`**: One-to-one relationship with WordPress post types (Courses, Lessons, Topics, Quizzes) via `post_id`.

**Validation Rules**:

*   `post_id` must be a valid existing WordPress post ID.
*   `word_count`, `estimated_minutes_average`, `estimated_minutes_slow` must be non-negative numeric values.

### Plugin Settings

Represents the configuration options for the WPC Seat Time Estimates plugin, stored in the WordPress Options API.

**Attributes**:

*   **`average_wpm`** (integer): The average reading speed in words per minute. Default: 200.
*   **`slow_wpm`** (integer): The slower reading speed in words per minute, used for range estimation. Default: 120.
*   **`display_label`** (string): A customizable label to precede the estimated seat time display (e.g., "Estimated Seat Time:", "Time to complete:").

**Validation Rules**:

*   `average_wpm` and `slow_wpm` must be positive integers. `average_wpm` must be greater than or equal to `slow_wpm`.
*   `display_label` can be any string, but should be sanitized.

