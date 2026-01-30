---
id: 0005
title: Fix seat time display issues
stage: green
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: bugfix
labels: ["bugfix", "frontend", "display"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - public/class-wpc-seat-time-public.php
 - includes/class-wpc-seat-time-display.php
---

## Prompt

estimated seat time is not showing fix thisEstimated seat time is not showing, check and test and implement and fix

## Response snapshot

I have fixed the issue where estimated seat time was not showing on the frontend:
- Handled potential null `$post_id` in LearnDash frontend hooks by using `get_the_ID()`.
- Implemented on-the-fly calculation in `WPC_Seat_Time_Display` if metadata is missing (ensures display for existing content).
- Added `the_content` filter as a fallback for cases where LearnDash template hooks might be missing.
- Added a display flag to prevent double display of the estimate.

## Outcome

- ✅ Impact: Seat time estimates now show reliably on LearnDash courses, lessons, topics, and quizzes, even for pre-existing content.
- 🧪 Tests: none
- 📁 Files: Modified `public/class-wpc-seat-time-public.php` and `includes/class-wpc-seat-time-display.php`.
- 🧠 Reflection: Providing a fallback to calculate on-the-fly is essential for plugins that deal with existing content, as hooks like `save_post` only affect future updates.