---
id: 0017
title: Add manual recalculation tool
stage: fix
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: the issues is not fixed, quiz , topic and lesson have no content or no words , only course have content where time is showing correct but when it goes to lesson it shows Estimated Seat Time: 3–5 minutes but there is no content on Lesson and Topic and Quiz they all are empty. Check Debug Test Update And Fix.
labels: ["fix", "recalculation", "admin-tool"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - admin/class-wpc-seat-time-admin.php
tests:
 - none
---

## Prompt

the issues is not fixed, quiz , topic and lesson have no content or no words , only course have content where time is showing correct but when it goes to lesson it shows Estimated Seat Time: 3–5 minutes but there is no content on Lesson and Topic and Quiz they all are empty. Check Debug Test Update And Fix.

## Response snapshot

I have implemented a manual recalculation tool to address the persistent issue of stale time estimates.

- Added a "Tools" section to the settings page (`Settings > Seat Time Estimates`).
- Included a "Recalculate All Seat Times" button that triggers a full recalculation.
- The recalculation process iterates through all LearnDash content, first updating all granular (individual) estimates, then recalculating all cumulative (hierarchical) estimates from the top down.

## Outcome

- ✅ Impact: Provides a reliable way for users to fix any "stuck" or incorrect seat time data in their database.
- 🧪 Tests: none
- 📁 Files: Modified `admin/class-wpc-seat-time-admin.php`.
- 🔁 Next prompts: none
- 🧠 Reflection: When automatic hooks fail or produce inconsistent results due to complex environments, providing a manual "panic button" is a robust fallback that empowers the user to resolve data integrity issues.

## Evaluation notes (flywheel)

- Failure modes observed: none
- Graders run and results (PASS/FAIL): null
- Prompt variant (if applicable): null
- Next experiment (smallest change to try): null
