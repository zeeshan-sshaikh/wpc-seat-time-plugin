"I need to update our implementation strategy for Phase 4: User Story 2. We are moving away from the 'Granular' display and switching to a 'Cumulative Display' model.

The goal is to ensure that seat time reflects the total time required for the current item plus everything nested inside it. Please update the roadmap and tasks to follow this hierarchy:

Expected Cumulative Logic:

Course Page: Total time of (Course Content + all Lessons + all Topics + all Quizzes).

Lesson Page: Total time of (Lesson Content + its nested Topics + its nested Quizzes).

Topic Page: Total time of (Topic Content + its nested Quizzes).

Quiz Page: Time for the individual Quiz.

New Task List to Implement:

- Extend class-wpc-seat-time-display.php to handle LearnDash lesson/topic hooks and display the calculated time.

- Add and verify Quiz support (sfwd-quiz) in both the display class and the calculator.

- Update the calculation logic to be CUMULATIVE. When viewing a Lesson or Topic, the display must show the sum of its own content plus all nested child elements.

Please confirm you understand this shift to Cumulative Seat Time, and let's start by refactoring the hooks from the public class into class-wpc-seat-time-display.php."