---
id: 0012
title: Fix persistent Estimated duration label
stage: fix
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: still showing when i go to the learndash course view "Estimated duration": 8–12 minutes instead of "Estimated Seat Time"
labels: ["fix", "database", "migration"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - wpc-seat-time-estimates.php
tests:
 - none
---

## Prompt

still showing when i go to the learndash course view "Estimated duration": 8–12 minutes instead of "Estimated Seat Time"

## Response snapshot

The issue was caused by the old label "Estimated duration:" being saved in the WordPress database, which the plugin prioritizes over the code default.

I have implemented an automatic upgrade routine in `wpc-seat-time-estimates.php` that runs on `admin_init`. It checks if the current setting is "Estimated duration:" and, if so, updates it to "Estimated Seat Time:". This ensures the fix is applied without manual user intervention in the settings page.

## Outcome

- ✅ Impact: Automatically fixes the displayed label for users with the old default saved in their database.
- 🧪 Tests: none
- 📁 Files: Updated `wpc-seat-time-estimates.php` to include the `upgrade_options` method.
- 🔁 Next prompts: none
- 🧠 Reflection: When changing defaults that are also saved options, a migration path is necessary to ensure existing installs are updated.

## Evaluation notes (flywheel)

- Failure modes observed: none
- Graders run and results (PASS/FAIL): null
- Prompt variant (if applicable): null
- Next experiment (smallest change to try): null
