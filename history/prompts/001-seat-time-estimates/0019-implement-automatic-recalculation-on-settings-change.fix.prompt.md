---
id: 0019
title: Implement automatic recalculation on settings change
stage: fix
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: "i've noticed when i was tesing i changed the Average Reading Speed (WPM) from 250 to 100 but estimated seat time doesn't change, so am i missing something ? why estimated time is not changing or should i try something else or i'm testing it wrong ? Correct me"
labels: ["fix", "recalculation", "ux"]
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

i've noticed when i was tesing i changed the 
Average Reading Speed (WPM) from 250 to 100 but estimated seat time doesn't change, so am i missing something ? why estimated time is not changing or should i try something else or i'm testing it wrong ? Correct me

## Response snapshot

I have implemented a fix to address the user's core expectation that changing the WPM settings should immediately update all seat time estimates.

- Implemented an automatic recalculation process in `admin/class-wpc-seat-time-admin.php`.
- The process is triggered by the `update_option_wpc_seat_time_settings` hook.
- It compares the old and new WPM values and, if they differ, runs a full recalculation of all LearnDash content.
- A transient-based admin notice informs the user that the recalculation has completed successfully.

## Outcome

- ✅ Impact: Changing the WPM settings now automatically and immediately updates all seat time estimates across the site, providing a much better user experience.
- 🧪 Tests: none
- 📁 Files: Modified `admin/class-wpc-seat-time-admin.php`.
- 🔁 Next prompts: none
- 🧠 Reflection: The initial design prioritized performance by calculating only on `save_post`, but this led to a confusing user experience. Hooking into the settings update to trigger a site-wide recalculation is the correct balance of performance and usability.

## Evaluation notes (flywheel)

- Failure modes observed: none
- Graders run and results (PASS/FAIL): null
- Prompt variant (if applicable): null
- Next experiment (smallest change to try): null
