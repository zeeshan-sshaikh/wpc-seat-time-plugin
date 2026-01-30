# Feature Specification: Seat Time Estimates for LearnDash

**Feature Branch**: `001-seat-time-estimates`  
**Created**: 2026-01-27  
**Status**: Draft  
**Input**: User description: "Automated calculation and display of estimated seat time based on word count for LearnDash courses, lessons, topics, and quizzes."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Viewing Course Seat Time (Priority: P1)

As a student, I want to see how long a course will take to complete before I start, so I can manage my time effectively.

**Why this priority**: This is the core functionality that provides immediate value to the end user and fulfills the primary requirement of the plugin.

**Independent Test**: Can be fully tested by visiting a Course page and observing the displayed duration estimate.

**Acceptance Scenarios**:

1. **Given** a LearnDash course with multiple lessons and topics, **When** a student views the course page, **Then** they see an estimated completion time (e.g., "Estimated Seat Time: 15–25 minutes").
2. **Given** a course with no content, **When** a student views the course page, **Then** no estimate or "0 minutes" is displayed.

---

### User Story 2 - Viewing Lesson/Topic Seat Time (Priority: P2)

As a student, I want to see the estimated time for an individual lesson or topic so I know if I have enough time to finish it in one sitting.

**Why this priority**: Improves user experience by providing more granular information as they progress through the course.

**Independent Test**: Can be tested by visiting individual lesson and topic pages and checking the duration display.

**Acceptance Scenarios**:

1. **Given** a LearnDash lesson with 1000 words, **When** a student views the lesson page, **Then** they see an estimate like "Estimated Seat Time: 5 minutes" (assuming 200 wpm).

---

### User Story 3 - Configuring Reading Speed (Priority: P3)

As an administrator, I want to be able to adjust the average reading speed used for calculations so the estimates are accurate for my specific audience.

**Why this priority**: Necessary for accuracy across different types of content (e.g., technical vs. general) and different student demographics.

**Independent Test**: Change the reading speed in settings and verify that the displayed estimates on the frontend update accordingly.

**Acceptance Scenarios**:

1. **Given** a course with 2000 words, **When** the admin sets reading speed to 200 wpm, **Then** the course shows "10 minutes".
2. **When** the admin sets reading speed to 100 wpm, **Then** the course shows "20 minutes".

### Edge Cases

- What happens when content consists only of images or videos? (Estimates should only count words; may show "0 mins" if no text).
- How does the system handle very large courses with hundreds of lessons? (Performance should remain stable).
- What if LearnDash is deactivated? (Plugin should fail gracefully or disable its functionality).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST calculate total word count for Courses, Lessons, Topics, and Quizzes by aggregating content from nested items.
- **FR-002**: System MUST strip all HTML tags, images, and non-text media before counting words.
- **FR-003**: System MUST display the estimated seat time on Course, Lesson, Topic, and Quiz frontend pages.
- **FR-004**: System MUST allow administrators to configure the "Average Reading Speed" and "Slow Reading Speed" in words per minute.
- **FR-005**: System MUST provide a range estimate (min-max) on the Course page based on the configured reading speeds.
- **FR-006**: System MUST automatically update calculations when content is saved in the WordPress editor.

### Key Entities *(include if feature involves data)*

- **Seat Time Estimate**: Represents the calculated duration for a specific post. Key attributes: Post ID, Word Count, Estimated Minutes (Average), Estimated Minutes (Slow).
- **Settings**: Plugin configuration. Key attributes: Average WPM (default 200), Slow WPM (default 120), Display Label.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Duration estimates are displayed on 100% of published LearnDash course/lesson/topic/quiz pages.
- **SC-002**: Calculation of word count for a 5,000-word lesson takes less than 100ms.
- **SC-003**: Administrators can update reading speeds and see changes reflected on the frontend immediately (or after cache clear).
- **SC-004**: HTML tags and media do not contribute to the word count (0% inclusion).

## Assumptions

- We assume LearnDash uses standard WordPress `post_content` for most course items.
- We assume word-based estimation is sufficient for Version 1, ignoring video/audio duration.