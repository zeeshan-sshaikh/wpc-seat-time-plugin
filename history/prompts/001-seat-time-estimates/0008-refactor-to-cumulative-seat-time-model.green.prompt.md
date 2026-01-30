---
id: 0008
title: Refactor to Cumulative Seat Time model
stage: green
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: /sp.implement
labels: ["refactor", "cumulative", "hierarchy"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - includes/class-wpc-seat-time-display.php
 - includes/class-wpc-seat-time-calculator.php
 - public/class-wpc-seat-time-public.php
 - specs/001-seat-time-estimates/tasks.md
---

## Prompt

"I need to update our implementation strategy for Phase 4: User Story 2. We are moving away from the 'Granular' display and switching to a 'Cumulative Display' model.

The goal is to ensure that seat time reflects the total time required for the current item plus everything nested inside it. Please update the roadmap and tasks to follow this hierarchy:

Expected Cumulative Logic:
Course Page: Total time of (Course Content + all Lessons + all Topics + all Quizzes).
Lesson Page: Total time of (Lesson Content + its nested Topics + its nested Quizzes).
Topic Page: Total time of (Topic Content + its nested Quizzes).
Quiz Page: Time for the individual Quiz.

New Task List to Implement:
- Extend class-wpc-seat-time-display.php to handle LearnDash lesson/topic hooks and display the calculated time.
- Add and verify Quiz support (sfwd-quiz) in both the display class and the calculator.
- Update the calculation logic to be CUMULATIVE. When viewing a Lesson or Topic, the display must show the sum of its own content plus all nested child elements.

Please confirm you understand this shift to Cumulative Seat Time, and let's start by refactoring the hooks from the public class into class-wpc-seat-time-display.php."

## Response snapshot

I have refactored the plugin to follow the **Cumulative Seat Time** model:
- **Centralized Hooks**: Moved all LearnDash display hooks and `the_content` fallback from `WPC_Seat_Time_Public` to `WPC_Seat_Time_Display`.
- **Hierarchical Aggregation**: Updated `WPC_Seat_Time_Calculator` to recursively calculate cumulative totals (including nested topics/quizzes) for Courses, Lessons, and Topics.
- **Quiz Support**: Fully integrated `sfwd-quiz` into calculation and display logic.
- **Dynamic Calculation**: `WPC_Seat_Time_Display` now triggers cumulative calculations on-the-fly if metadata is missing.

## Outcome

- ✅ Impact: Users now see the total time required for any part of the course hierarchy (Cumulative Display).
- 🧪 Tests: none
- 📁 Files: Refactored 3 PHP classes and updated `tasks.md`.
- 🧠 Reflection: Centralizing display logic in the `Display` class and aggregation in the `Calculator` class provides a cleaner architecture for hierarchical data.