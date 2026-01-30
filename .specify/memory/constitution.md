<!--
Sync Impact Report:
- Version Change: 1.0.0 (Initial Draft)
- Principles Defined:
  - I. WordPress Standards Compliance (New)
  - II. Performance & Caching (New)
  - III. MVP First (Iterative Development) (New)
  - IV. User Privacy & Security (New)
  - V. Native UI Integration (New)
- Templates Checked: plan-template.md, spec-template.md, tasks-template.md (No blocking conflicts found).
-->
# WPC Seat Time Estimates for LearnDash Constitution

## Core Principles

### I. WordPress Standards Compliance
Adhere strictly to WordPress Coding Standards (WPCS) and LearnDash best practices. Use established WordPress APIs (Settings, Metadata, Plugin, Transients) rather than custom solutions where possible. Ensure compatibility with the latest versions of WordPress and LearnDash.

### II. Performance & Caching
Calculations (like seat time estimates) MUST NOT run on every page load. Expensive operations must be calculated asynchronously or on specific hooks (e.g., `save_post`) and stored (in `post_meta` or Transients) for efficient retrieval. Front-end impact must be negligible.

### III. MVP First (Iterative Development)
Start with a focused Minimum Viable Product: basic word count-based estimation for text content. Complex features like video duration parsing or custom reading speeds should be added in subsequent iterations. Ship small, testable increments.

### IV. User Privacy & Security
Sanitize all inputs (`sanitize_text_field`, `absint`) and escape all outputs (`esc_html`, `esc_attr`). Verify user capabilities (`current_user_can`) before allowing settings changes. Do not collect sensitive user data unless explicitly required and consented.

### V. Native UI Integration
The plugin should feel like a native part of WordPress and LearnDash. Settings should reside in standard locations (LearnDash settings or native submenus). Frontend estimates should be injected via standard hooks/filters, matching the theme's styling.

## Technical Constraints

### Environment
- **PHP**: 7.4+ (Compatible with modern WP)
- **WordPress**: 6.0+
- **LearnDash**: 4.0+
- **Local Dev**: XAMPP/macOS compatible (per agent skills)

### Architecture
- **Structure**: Standard WordPress Plugin structure.
- **Namespacing**: Use a unique prefix (e.g., `WPC_Seat_Time`) for functions/classes to avoid collisions.

## Development Workflow

### Quality Gates
- **Linting**: Code must pass `phpcs` (WordPress-Core standard).
- **Testing**: Manual verification of estimates on Course/Lesson/Topic post types.

## Governance

### Amendment Process
Principles can be amended by consensus. Changes to "Core Principles" require a MINOR version bump. Clarifications require a PATCH bump.

### Compliance
All code contributions must be reviewed against these principles. Non-compliant code (e.g., direct DB queries where an API exists, unsanitized input) must be rejected.

**Version**: 1.0.0 | **Ratified**: 2026-01-27 | **Last Amended**: 2026-01-27