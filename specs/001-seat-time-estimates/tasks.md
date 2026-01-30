# Tasks: Seat Time Estimates for LearnDash

Feature: `001-seat-time-estimates`

## Phase 1: Setup

**Goal**: Initialize the plugin structure and basic metadata to ensure it can be activated within WordPress.

**Checkpoint**: Plugin is visible in the WordPress admin and can be activated without errors, setting default options upon activation.

### Implementation for Setup

- [X] T001 Create plugin directory structure in root
- [X] T002 Create main plugin file `wpc-seat-time-estimates.php` with headers and basic class loader
- [X] T003 Implement activation hook to set default options in `wpc-seat-time-estimates.php`

## Phase 2: Foundational

**Goal**: Build the core logic for retrieving settings, calculating word counts/durations, and handling data storage via hooks.

**Checkpoint**: Saving a LearnDash post triggers a calculation, and the resulting word count and estimated minutes are correctly stored in `post_meta`.

### Implementation for Foundational

- [X] T004 [P] Create `WPC_Seat_Time_Settings` class in `includes/class-wpc-seat-time-settings.php` to handle option retrieval
- [X] T005 [P] Create `WPC_Seat_Time_Calculator` class in `includes/class-wpc-seat-time-calculator.php` with word count and time calculation logic
- [X] T006 Implement `save_post` hook logic to trigger calculation for LearnDash post types in `includes/class-wpc-seat-time-calculator.php`
- [X] T007 [P] Create `WPC_Seat_Time_Display` class in `includes/class-wpc-seat-time-display.php` for formatting duration strings

## Phase 3: [US1] Viewing Course Seat Time (P1) 🎯 MVP

**Goal**: As a student, I want to see how long a course will take to complete before I start, so I can manage my time effectively.

**Independent Test**: Can be fully tested by visiting a Course page and observing the displayed duration estimate.

**Checkpoint**: Course pages display an aggregated time estimate based on all nested lessons, topics, and quizzes.

### Implementation for User Story 1 (Cumulative Aggregation)

- [X] T008 [US1] Implement Course-level aggregation logic in `includes/class-wpc-seat-time-calculator.php` (summing child lesson/topic times)
- [X] T009 [US1] Create frontend hook class `WPC_Seat_Time_Public` in `public/class-wpc-seat-time-public.php`
- [X] T010 [US1] Register LearnDash course template hooks to display estimates in `public/class-wpc-seat-time-public.php`

## Phase 4: [US2] Viewing Cumulative Seat Time (P2)

**Goal**: As a student, I want to see the total estimated time for a lesson or topic, including all its nested content (topics/quizzes), to better plan my study sessions.

**Independent Test**: Visit a Lesson page that has topics and quizzes; verify the displayed time is the sum of the lesson itself plus all children.

**Checkpoint**: Cumulative aggregation is functional for Lessons and Topics, reflecting the full hierarchy accurately.

### Implementation for User Story 2 (Cumulative Display)

- [X] T011 [US2] Refactor LearnDash hooks from `WPC_Seat_Time_Public` to `WPC_Seat_Time_Display` for centralized management
- [X] T012 [US2] Extend `WPC_Seat_Time_Calculator` to support recursive aggregation for Lessons (Lesson + Topics + Quizzes)
- [X] T013 [US2] Extend `WPC_Seat_Time_Calculator` to support recursive aggregation for Topics (Topic + Quizzes)
- [X] T014 [US2] Add support for Quizzes (`sfwd-quiz`) in both calculator and display logic
- [X] T015 [US2] Update `WPC_Seat_Time_Display` to dynamically fetch the correct cumulative total based on the current hierarchy level

## Phase 5: [US3] Configuring Reading Speed (P3)

**Goal**: As an administrator, I want to be able to adjust the average reading speed used for calculations so the estimates are accurate for my specific audience.

**Independent Test**: Change the reading speed in settings and verify that the displayed estimates on the frontend update accordingly.

**Checkpoint**: Admin settings page is functional, allowing customization of reading speeds and labels that immediately affect frontend calculations.

### Implementation for User Story 3 (Admin Configuration)

- [X] T013 [US3] Create admin settings class `WPC_Seat_Time_Admin` in `admin/class-wpc-seat-time-admin.php` using WordPress Settings API
- [X] T014 [US3] Implement settings fields for Average WPM, Slow WPM, and Display Label in `admin/class-wpc-seat-time-admin.php`
- [X] T015 [US3] Add validation and sanitization for admin settings in `admin/class-wpc-seat-time-admin.php`

## Phase 6: Polish & Cross-cutting

**Goal**: Ensure clean uninstallation, security compliance, and seamless integration with LearnDash's template system.

**Checkpoint**: Plugin can be uninstalled without leaving orphaned data, and all outputs are properly escaped and secured.

### Implementation for Polish

- [X] T016 Create `uninstall.php` to remove plugin options and post meta on deletion
- [X] T017 Final review of input sanitization and output escaping across all files
- [X] T018 Verify compatibility with LearnDash template hierarchy

## Dependencies
- Phase 2 must be completed before User Story phases.
- [US1] and [US2] depend on [US3] settings, but can use default values from Phase 1 if needed.
- [US1] (Course) depends on [US2] (Lessons/Topics) if course time is calculated by aggregation.

## Parallel Execution Examples
- T004, T005, and T007 can be developed simultaneously.
- Admin (Phase 5) and Public (Phases 3/4) can be developed independently once Foundation (Phase 2) is stable.

## Implementation Strategy
- **MVP**: Complete Phase 1, 2, and 3 to provide value with course-level estimates.
- **Incremental**: Add lesson-level estimates (Phase 4) followed by admin configurability (Phase 5).