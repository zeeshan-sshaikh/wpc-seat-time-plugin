---
id: 0009
title: Fix hierarchical seat time updates
stage: green
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: bugfix
labels: ["bugfix", "hierarchy", "calculation"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - includes/class-wpc-seat-time-calculator.php
---

## Prompt

when i add some text in lesson or topic or quiz the course estimated time doesn't change, same goes for lesson and topic

## Response snapshot

I have fixed the hierarchical update issue where parent estimates (Courses, Lessons, Topics) were not refreshing when child content (Quizzes, Topics) was updated:
- **Improved `update_parents`**: Correctly handles the full hierarchy, including Quiz -> Topic -> Lesson -> Course bubbling.
- **Recursive Calculation**: `calculate_cumulative_estimates` now recursively ensures all children have valid estimates before summing them, preventing "zero-sum" errors on the first run or after clearing cache.
- **Reliable Lookups**: Switched to direct meta-key lookups for parent IDs to ensure data is available during the `save_post` hook.

## Outcome

- ✅ Impact: Changes to child content now correctly propagate up the hierarchy to update Lesson and Course totals immediately.
- 🧪 Tests: none
- 📁 Files: Modified `includes/class-wpc-seat-time-calculator.php`.
- 🧠 Reflection: Hierarchical data requires strict "bottom-up" propagation on update and "top-down" verification on read to ensure consistency.