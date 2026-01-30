# Research for WPC Seat Time Estimates for LearnDash

## Phase 0: Outline & Research

### Research Task: Word Counting and HTML Stripping Libraries/Methods

**Decision**: NEEDS CLARIFICATION
**Rationale**: Determining the most efficient and robust way to count words and strip HTML from WordPress `post_content` is crucial for accuracy, performance, and security. Native WordPress functions, custom regex, or external libraries need to be evaluated.
**Alternatives considered**:
*   `strip_tags()` + `str_word_count()` (native PHP)
*   `wp_strip_all_tags()` + custom word count logic (WordPress native)
*   External HTML parser libraries (e.g., HTML Purifier - might be overkill or performance heavy)
*   Custom regex for stripping specific shortcodes/elements.
