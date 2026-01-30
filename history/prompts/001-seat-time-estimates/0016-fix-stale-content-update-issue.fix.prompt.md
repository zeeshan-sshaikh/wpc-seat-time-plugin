---
id: 0016
title: Fix stale content update issue
stage: fix
date: 2026-01-29
surface: agent
model: gemini-2.0-flash
feature: 001-seat-time-estimates
branch: 001-seat-time-estimates
user: zeeshan
command: i remove the lesson content but still on top its showing Estimated Seat Time: 3–5 minutes fix this
labels: ["fix", "cache", "calculation"]
links:
  spec: specs/001-seat-time-estimates/spec.md
  ticket: null
  adr: null
  pr: null
files:
 - includes/class-wpc-seat-time-calculator.php
tests:
 - none
---

## Prompt

i remove the lesson content but still on top its showing Estimated Seat Time: 3–5 minutes fix this

## Response snapshot

I have fixed the issue where removing lesson content did not immediately update the seat time estimate.
- Implemented `clean_post_cache()` in `calculate_granular_estimates` to force a cache refresh.
- Updated `calculate_cumulative_estimates` to accept override values.
- Modified `handle_save_post` to pass the freshly calculated granular estimates directly to the cumulative calculator, bypassing potentially stale database reads.

## Outcome

- ✅ Impact: Immediate and accurate updates when content is removed or changed.
- 🧪 Tests: none
- 📁 Files: Modified `includes/class-wpc-seat-time-calculator.php`.
- 🔁 Next prompts: none
- 🧠 Reflection: Relying on `get_post_meta` immediately after `update_post_meta` can sometimes yield stale results due to object caching. Passing values directly is more robust.

## Evaluation notes (flywheel)

- Failure modes observed: none
- Graders run and results (PASS/FAIL): null
- Prompt variant (if applicable): null
- Next experiment (smallest change to try): null
