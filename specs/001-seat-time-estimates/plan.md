# Implementation Plan: WPC Seat Time Estimates for LearnDash

**Branch**: `001-seat-time-estimates` | **Date**: 2026-01-27 | **Spec**: specs/001-seat-time-estimates/spec.md
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/sp.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Automated calculation and display of estimated seat time based on word count for LearnDash courses, lessons, topics, and quizzes.

## Technical Context

**Language/Version**: PHP 7.4+
**Primary Dependencies**: WordPress, LearnDash, PHPUnit (for testing)
**Storage**: WordPress `post_meta` (for estimated seat times per post), WordPress Options API (for plugin settings)
**Testing**: PHPUnit for unit tests, Manual verification on Course/Lesson/Topic post types.
**Target Platform**: WordPress 6.0+ on PHP 7.4+
**Project Type**: WordPress Plugin
**Performance Goals**:
*   Calculation of word count for a 5,000-word lesson takes less than 100ms.
*   Front-end impact must be negligible.
**Constraints**:
*   Strict adherence to WordPress Coding Standards (WPCS) and LearnDash best practices.
*   No custom database tables.
*   Calculations must be asynchronous or hook-based, not on every page load.
*   All inputs must be sanitized, and outputs escaped for security.
*   Compatibility with latest WordPress and LearnDash versions.
**Scale/Scope**:
*   Estimated seat times for LearnDash Courses, Lessons, Topics, and Quizzes.
*   Handles lessons up to 5,000 words.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

*   **I. WordPress Standards Compliance**: Evaluate adherence to WPCS and LearnDash best practices.
*   **II. Performance & Caching**: Evaluate calculation strategy and front-end impact.
*   **III. MVP First (Iterative Development)**: Ensure initial scope aligns with MVP.
*   **IV. User Privacy & Security**: Verify input sanitization and output escaping, capability checks.
*   **V. Native UI Integration**: Assess integration with WordPress/LearnDash UI.

## Project Structure

### Documentation (this feature)

```text
specs/001-seat-time-estimates/
├── plan.md              # This file (/sp.plan command output)
├── research.md          # Phase 0 output (/sp.plan command)
├── data-model.md        # Phase 1 output (/sp.plan command)
├── quickstart.md        # Phase 1 output (/sp.plan command)
├── contracts/           # Phase 1 output (/sp.plan command)
└── tasks.md             # Phase 2 output (/sp.tasks command - NOT created by /sp.plan)
```

### Source Code (repository root)

```text
wpc-seat-time-estimates/ (plugin root, will be the current working directory)
├───includes/            # Core plugin logic, classes
│   ├───class-wpc-seat-time-calculator.php
│   ├───class-wpc-seat-time-display.php
│   └───class-wpc-seat-time-settings.php
├───admin/               # Admin-specific files
│   └───class-wpc-seat-time-admin.php
├───public/              # Frontend-specific files
│   └───class-wpc-seat-time-public.php
├───assets/              # CSS/JS if needed for admin/public
│   ├───css/
│   └───js/
├───templates/           # If any template overrides are needed
├───wpc-seat-time-estimates.php # Main plugin file
└───uninstall.php        # Plugin uninstallation script
```

**Structure Decision**: The project will follow a standard WordPress plugin structure, organized into `includes`, `admin`, and `public` directories for clear separation of concerns. The main plugin file (`wpc-seat-time-estimates.php`) will handle initialization and loading of other components.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
