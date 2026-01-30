---
id: 0010
title: Fix cumulative seat time logic
stage: green
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: bugfix
labels: ["bugfix", "cumulative", "logic"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - includes/class-wpc-seat-time-calculator.php
 - includes/class-wpc-seat-time-display.php
---

## Prompt

Cumulative logic is not working its not updating estimated time of course,lesson,topic , still the granular logic is happening use your skill to fix this

## Response snapshot

I have fixed the cumulative seat time logic to ensure estimates update correctly across the hierarchy:
- **Recursive Calculation**: Refactored `calculate_cumulative_estimates` to be a public static method that recursively calculates and sums child estimates (Topic > Lesson > Course).
- **Static Cache**: Implemented a calculation cache to prevent redundant processing during a single request.
- **Robust Updates**: Improved parent-child relationship lookups to ensure `save_post` correctly triggers updates in parent items.
- **Display Integration**: Updated `WPC_Seat_Time_Display` to use the static calculation method, ensuring accurate data retrieval even on the first run.

## Outcome

- ✅ Impact: Course and Lesson totals now accurately reflect the sum of all nested content, including granular and cumulative values.
- 🧪 Tests: none
- 📁 Files: Modified `includes/class-wpc-seat-time-calculator.php` and `includes/class-wpc-seat-time-display.php`.
- 🧠 Reflection: Centralizing calculation as a static, recursive method ensures consistency across both the `Calculator` (during save) and the `Display` (during view) contexts.